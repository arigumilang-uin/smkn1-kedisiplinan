<?php

namespace App\Services\Pelanggaran;

use App\Models\JenisPelanggaran;
use App\Models\RiwayatPelanggaran;
use App\Models\Siswa;
use App\Models\TindakLanjut;
use App\Notifications\TindakLanjutNotificationService;

/**
 * Service untuk Rules Engine Pelanggaran (v2.0 - Frequency-Based)
 *
 * Tanggung jawab:
 * - Mengevaluasi poin berdasarkan threshold frekuensi
 * - Menentukan jenis surat berdasarkan pembina yang terlibat
 * - Membuat/update TindakLanjut dan SuratPanggilan otomatis
 * - Memberikan rekomendasi pembinaan internal (TIDAK trigger surat)
 * - Trigger notifikasi untuk approval workflow
 */
class PelanggaranRulesEngine
{
    /**
     * @var TindakLanjutNotificationService
     */
    protected $notificationService;

    /**
     * Constructor dengan dependency injection.
     */
    public function __construct(TindakLanjutNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Konstanta tipe surat (eskalasi levels)
     */
    const SURAT_1 = 'Surat 1';
    const SURAT_2 = 'Surat 2';
    const SURAT_3 = 'Surat 3';
    const SURAT_4 = 'Surat 4';

    /**
     * Proses batch pelanggaran untuk satu siswa.
     * Dipanggil saat pencatatan multiple pelanggaran.
     *
     * @param int $siswaId
     * @param array $pelanggaranIds Array ID jenis pelanggaran
     * @return void
     */
    public function processBatch(int $siswaId, array $pelanggaranIds): void
    {
        $siswa = Siswa::find($siswaId);
        if (!$siswa) return;

        // Eager load frequency rules
        $pelanggaranObjs = JenisPelanggaran::with('frequencyRules')
            ->whereIn('id', $pelanggaranIds)
            ->get();

        if ($pelanggaranObjs->isEmpty()) return;

        $totalPoinBaru = 0;
        $suratTypes = [];
        $sanksiList = [];
        $pembinaRolesForSurat = [];

        // Evaluasi setiap pelanggaran
        foreach ($pelanggaranObjs as $pelanggaran) {
            if ($pelanggaran->usesFrequencyRules()) {
                // Gunakan frequency-based evaluation
                $result = $this->evaluateFrequencyRules($siswaId, $pelanggaran);
                $totalPoinBaru += $result['poin_ditambahkan'];

                if ($result['surat_type']) {
                    $suratTypes[] = $result['surat_type'];
                    // Simpan pembina roles dari rule yang trigger surat
                    $pembinaRolesForSurat = $result['pembina_roles'] ?? [];
                }

                $sanksiList[] = $result['sanksi'];
            } else {
                // Fallback: immediate accumulation (backward compatibility)
                $totalPoinBaru += $pelanggaran->poin;
                
                // REMOVED: Auto-trigger surat berdasarkan poin
                // REASON: Surat HANYA trigger dari frequency rules dengan trigger_surat=true
                // Pembinaan Internal (akumulasi poin) HANYA untuk rekomendasi, TIDAK trigger surat
                
                // Sanksi description for fallback
                $sanksiList[] = $pelanggaran->nama_pelanggaran;
            }
        }

        // Tentukan tipe surat tertinggi (HANYA dari frequency rules)
        $tipeSurat = $this->tentukanTipeSuratTertinggi($suratTypes);

        // Buat/update TindakLanjut jika diperlukan
        if ($tipeSurat) {
            $pemicu = implode(', ', array_unique(array_filter($sanksiList)));
            
            // Tentukan status berdasarkan pembina yang terlibat (bukan tipe surat)
            $status = $this->tentukanStatusBerdasarkanPembina($pembinaRolesForSurat);

            $this->buatAtauUpdateTindakLanjut($siswaId, $tipeSurat, $pemicu, $status, $pembinaRolesForSurat);
        }
    }

    /**
     * Evaluasi frequency rules untuk satu siswa dan satu jenis pelanggaran.
     *
     * @param int $siswaId
     * @param JenisPelanggaran $pelanggaran
     * @return array ['poin_ditambahkan' => int, 'surat_type' => string|null, 'sanksi' => string, 'pembina_roles' => array]
     */
    private function evaluateFrequencyRules(int $siswaId, JenisPelanggaran $pelanggaran): array
    {
        // Hitung frekuensi total pelanggaran ini untuk siswa
        $currentFrequency = RiwayatPelanggaran::where('siswa_id', $siswaId)
            ->where('jenis_pelanggaran_id', $pelanggaran->id)
            ->count();

        // Ambil semua frequency rules untuk pelanggaran ini
        $rules = $pelanggaran->frequencyRules;

        if ($rules->isEmpty()) {
            // Fallback: tidak ada rules, gunakan poin langsung
            return [
                'poin_ditambahkan' => $pelanggaran->poin,
                'surat_type' => null,
                'sanksi' => 'Pembinaan',
                'pembina_roles' => [],
            ];
        }

        // Cari rule yang TEPAT match dengan frekuensi saat ini
        // matchesFrequency() sekarang check EXACT match (frequency === max, atau === min jika no max)
        $matchedRule = $rules->first(function ($rule) use ($currentFrequency) {
            return $rule->matchesFrequency($currentFrequency);
        });

        if (!$matchedRule) {
            // Tidak ada rule yang match, tidak ada poin (belum mencapai threshold)
            return [
                'poin_ditambahkan' => 0,
                'surat_type' => null,
                'sanksi' => 'Belum mencapai threshold',
                'pembina_roles' => [],
            ];
        }

        // Threshold tercapai! Tambahkan poin
        return [
            'poin_ditambahkan' => $matchedRule->poin,
            'surat_type' => $matchedRule->getSuratType(),
            'sanksi' => $matchedRule->sanksi_description,
            'pembina_roles' => $matchedRule->pembina_roles ?? [],
        ];
    }

    /**
     * Tentukan tipe surat tertinggi dari array surat types.
     * Prioritas: Surat 4 > Surat 3 > Surat 2 > Surat 1
     *
     * CATATAN PENTING: Akumulasi poin TIDAK trigger surat otomatis!
     * Surat HANYA dari frequency rules yang memiliki trigger_surat = TRUE
     *
     * @param array $suratTypes
     * @return string|null
     */
    private function tentukanTipeSuratTertinggi(array $suratTypes): ?string
    {
        if (empty($suratTypes)) {
            // Tidak ada surat dari frequency rules
            return null;
        }

        // Extract level dari surat types
        $levels = array_map(function ($surat) {
            return (int) filter_var($surat, FILTER_SANITIZE_NUMBER_INT);
        }, $suratTypes);

        $maxLevel = max($levels);

        return $maxLevel > 0 ? "Surat {$maxLevel}" : null;
    }

    /**
     * Tentukan status tindak lanjut berdasarkan pembina yang terlibat.
     * 
     * Business Rule:
     * - Jika Kepala Sekolah terlibat sebagai pembina → Menunggu Persetujuan
     * - Jika hanya pembina level bawah (Wali Kelas, Kaprodi, Waka) → Baru
     * 
     * Filosofi: Kepala Sekolah harus approve keterlibatannya sendiri dalam pembinaan.
     * Ini lebih fleksibel dan konsisten dibanding hardcoded berdasarkan tipe surat.
     * 
     * Contoh:
     * - Wali Kelas + Kaprodi (Surat 2) → Status: Baru
     * - Wali Kelas + Kaprodi + Waka (Surat 3) → Status: Baru
     * - Wali Kelas + Kaprodi + Waka + Kepsek (Surat 4) → Status: Menunggu Persetujuan
     * - Wali Kelas + Kepsek (Surat 2) → Status: Menunggu Persetujuan (edge case handled!)
     * 
     * @param array $pembinaRoles Array of pembina role names
     * @return string Status: 'Menunggu Persetujuan' atau 'Baru'
     */
    private function tentukanStatusBerdasarkanPembina(array $pembinaRoles): string
    {
        // Jika Kepala Sekolah terlibat sebagai pembina, butuh approval
        if (in_array('Kepala Sekolah', $pembinaRoles)) {
            return 'Menunggu Persetujuan';
        }
        
        // Jika hanya pembina level bawah, langsung proses
        return 'Baru';
    }

    /**
     * Tentukan rekomendasi pembina untuk pembinaan internal berdasarkan akumulasi poin.
     * CATATAN: Ini HANYA rekomendasi konseling, TIDAK trigger surat pemanggilan.
     * 
     * LOGIC GAP HANDLING:
     * - UI menampilkan gap (0-50, 55-100, 105-300, 305-500) sesuai tata tertib
     * - Sistem otomatis isi gap dengan rule sebelumnya
     * - Contoh: Poin 53 → Masuk rule "0-50" (karena belum sampai 55)
     * - Contoh: Poin 303 → Masuk rule "105-300" (karena belum sampai 305)
     *
     * @param int $totalPoin Total poin akumulasi siswa
     * @return array ['pembina_roles' => array, 'keterangan' => string, 'range_text' => string]
     */
    public function getPembinaanInternalRekomendasi(int $totalPoin): array
    {
        // Query rules dari database (ordered by display_order)
        $rules = \App\Models\PembinaanInternalRule::orderBy('display_order')->get();

        if ($rules->isEmpty()) {
            return [
                'pembina_roles' => [],
                'keterangan' => 'Tidak ada pembinaan',
                'range_text' => 'N/A',
            ];
        }

        // Cari rule yang match dengan total poin
        // Logic: Cari rule terakhir yang poin_min <= totalPoin
        $matchedRule = null;
        
        foreach ($rules as $rule) {
            // Jika poin siswa >= poin_min rule ini
            if ($totalPoin >= $rule->poin_min) {
                // Jika rule ini punya poin_max dan poin siswa <= poin_max, match!
                if ($rule->poin_max !== null && $totalPoin <= $rule->poin_max) {
                    $matchedRule = $rule;
                    break;
                }
                
                // Jika rule ini open-ended (poin_max = null), match!
                if ($rule->poin_max === null) {
                    $matchedRule = $rule;
                    break;
                }
                
                // Jika poin siswa > poin_max rule ini, cek apakah ada rule berikutnya
                $nextRule = $rules->where('display_order', '>', $rule->display_order)->first();
                
                // Jika tidak ada rule berikutnya, atau poin siswa < poin_min rule berikutnya
                // Maka gunakan rule ini (untuk handle gap)
                if (!$nextRule || $totalPoin < $nextRule->poin_min) {
                    $matchedRule = $rule;
                    break;
                }
            }
        }

        if (!$matchedRule) {
            return [
                'pembina_roles' => [],
                'keterangan' => 'Tidak ada pembinaan',
                'range_text' => 'N/A',
            ];
        }

        return [
            'pembina_roles' => $matchedRule->pembina_roles,
            'keterangan' => $matchedRule->keterangan,
            'range_text' => $matchedRule->getRangeText(),
        ];
    }

    /**
     * Hitung total poin akumulasi siswa dari semua riwayat pelanggaran.
     * 
     * UPDATED LOGIC:
     * - For frequency-based rules: Calculate using evaluateFrequencyRules()
     * - For legacy rules: Use poin from jenis_pelanggaran table
     * 
     * @param int $siswaId
     * @return int
     */
    public function hitungTotalPoinAkumulasi(int $siswaId): int
    {
        // Get ALL riwayat for this siswa, grouped by jenis_pelanggaran
        $riwayat = RiwayatPelanggaran::where('siswa_id', $siswaId)
            ->with('jenisPelanggaran.frequencyRules')
            ->get()
            ->groupBy('jenis_pelanggaran_id');

        $totalPoin = 0;

        // For each jenis pelanggaran, calculate poin based on its rules
        foreach ($riwayat as $jenisPelanggaranId => $records) {
            $jenisPelanggaran = $records->first()?->jenisPelanggaran;
            
            if (!$jenisPelanggaran) continue;

            if ($jenisPelanggaran->usesFrequencyRules()) {
                // FREQUENCY-BASED: Iterate through ALL frequencies and sum matched poin
                // FIX: Previous logic only checked current frequency, missing cumulative poin
                $currentFrequency = $records->count();
                $rules = $jenisPelanggaran->frequencyRules;
                
                // Iterate from frequency 1 to current
                for ($freq = 1; $freq <= $currentFrequency; $freq++) {
                    // Check each rule if it matches this frequency
                    foreach ($rules as $rule) {
                        if ($rule->matchesFrequency($freq)) {
                            $totalPoin += $rule->poin;
                        }
                    }
                }
            } else {
                // LEGACY: Sum poin from all records (backward compatibility)
                $totalPoin += $records->count() * $jenisPelanggaran->poin;
            }
        }

        return $totalPoin;
    }

    /**
     * Get siswa yang perlu pembinaan berdasarkan akumulasi poin.
     * 
     * UPDATED: Use frequency-based point calculation (hitungTotalPoinAkumulasi)
     * Old method used simple SUM which doesn't work with frequency rules!
     * 
     * @param int|null $poinMin Filter minimum poin (optional)
     * @param int|null $poinMax Filter maximum poin (optional)
     * @return \Illuminate\Support\Collection
     */
    public function getSiswaPerluPembinaan(?int $poinMin = null, ?int $poinMax = null)
    {
        // Fetch pembinaan rules ONCE
        $rules = \App\Models\PembinaanInternalRule::orderBy('display_order')->get();
        
        // Get ALL siswa yang punya riwayat pelanggaran
        // Then calculate poin using frequency-based logic
        $siswaIds = \App\Models\RiwayatPelanggaran::distinct()
            ->pluck('siswa_id');
        
        // Calculate poin for each siswa using correct frequency logic
        $siswaList = collect();
        
        foreach ($siswaIds as $siswaId) {
            // Use the CORRECT frequency-based calculation
            $totalPoin = $this->hitungTotalPoinAkumulasi($siswaId);
            
            // Skip if poin is 0
            if ($totalPoin == 0) {
                continue;
            }
            
            // Apply poin filters
            if ($poinMin !== null && $totalPoin < $poinMin) {
                continue;
            }
            if ($poinMax !== null && $totalPoin > $poinMax) {
                continue;
            }
            
            // Get rekomendasi
            $rekomendasi = $this->getPembinaanInternalRekomendasiOptimized($totalPoin, $rules);
            
            // Skip if no matching rule (no recommendation)
            if (empty($rekomendasi['pembina_roles'])) {
                continue;
            }
            
            // Load siswa with relations
            $siswa = Siswa::with(['kelas.jurusan', 'kelas.waliKelas'])->find($siswaId);
            
            if (!$siswa) {
                continue;
            }
            
            $siswaList->push([
                'siswa' => $siswa,
                'total_poin' => $totalPoin,
                'rekomendasi' => $rekomendasi,
            ]);
        }
        
        return $siswaList->sortByDesc('total_poin')->values();
    }
    
    /**
     * OPTIMIZED VERSION: Process recommendation using pre-fetched rules collection
     * This eliminates the N queries to pembinaan_internal_rules table
     * 
     * @param int $totalPoin
     * @param \Illuminate\Support\Collection $rules Pre-fetched rules collection
     * @return array
     */
    protected function getPembinaanInternalRekomendasiOptimized(int $totalPoin, $rules): array
    {
        if ($rules->isEmpty()) {
            return [
                'pembina_roles' => [],
                'keterangan' => 'Tidak ada pembinaan',
                'range_text' => 'N/A',
            ];
        }

        // Cari rule yang match dengan total poin
        $matchedRule = null;
        
        foreach ($rules as $rule) {
            if ($totalPoin >= $rule->poin_min) {
                if ($rule->poin_max !== null && $totalPoin <= $rule->poin_max) {
                    $matchedRule = $rule;
                    break;
                }
                
                if ($rule->poin_max === null) {
                    $matchedRule = $rule;
                    break;
                }
                
                $nextRule = $rules->where('display_order', '>', $rule->display_order)->first();
                
                if (!$nextRule || $totalPoin < $nextRule->poin_min) {
                    $matchedRule = $rule;
                    break;
                }
            }
        }

        if (!$matchedRule) {
            return [
                'pembina_roles' => [],
                'keterangan' => 'Tidak ada pembinaan',
                'range_text' => 'N/A',
            ];
        }

        return [
            'pembina_roles' => $matchedRule->pembina_roles,
            'keterangan' => $matchedRule->keterangan,
            'range_text' => $matchedRule->getRangeText(),
        ];
    }

    /**
     * Buat atau update TindakLanjut dan SuratPanggilan untuk siswa.
     *
     * @param int $siswaId
     * @param string $tipeSurat
     * @param string $pemicu
     * @param string $status
     * @param array $pembinaRoles Array of pembina roles from frequency rule
     * @return void
     */
    private function buatAtauUpdateTindakLanjut(
        int $siswaId,
        string $tipeSurat,
        string $pemicu,
        string $status,
        array $pembinaRoles = []
    ): void {
        $sanksi = "Pemanggilan Wali Murid ({$tipeSurat})";

        // Cari kasus aktif siswa
        $kasusAktif = TindakLanjut::with('suratPanggilan')
            ->where('siswa_id', $siswaId)
            ->whereIn('status', ['Baru', 'Menunggu Persetujuan', 'Disetujui', 'Ditangani'])
            ->latest()
            ->first();

        if (!$kasusAktif) {
            try {
                // Buat TindakLanjut baru
                $tl = TindakLanjut::create([
                    'siswa_id' => $siswaId,
                    'pemicu' => $pemicu,
                    'sanksi_deskripsi' => $sanksi,
                    'status' => $status,
                ]);

                // Build pembina data menggunakan service
                $siswa = Siswa::with(['kelas.waliKelas', 'kelas.jurusan.kaprodi'])->find($siswaId);
                
                if (!$siswa) {
                    \Log::error("Siswa not found for TindakLanjut", ['siswa_id' => $siswaId]);
                    throw new \Exception("Siswa dengan ID {$siswaId} tidak ditemukan");
                }
                
                $suratService = new SuratPanggilanService();
                $pembinaData = $suratService->buildPembinaData($pembinaRoles, $siswa);
                
                if (empty($pembinaData)) {
                    \Log::warning("Pembina data is empty", [
                        'siswa_id' => $siswaId,
                        'pembina_roles' => $pembinaRoles
                    ]);
                }
                
                $meetingSchedule = $suratService->setDefaultMeetingSchedule();

                // Buat SuratPanggilan dengan pembina data
                $tl->suratPanggilan()->create([
                    'nomor_surat' => $suratService->generateNomorSurat(),
                    'tipe_surat' => $tipeSurat,
                    'tanggal_surat' => now(),
                    'pembina_data' => $pembinaData,
                    'tanggal_pertemuan' => $meetingSchedule['tanggal_pertemuan'],
                    'waktu_pertemuan' => $meetingSchedule['waktu_pertemuan'],
                    'keperluan' => $pemicu,
                ]);

                // Trigger notifikasi jika butuh approval (Surat 3 & 4)
                $this->notificationService->notifyKasusButuhApproval($tl);
                
                // Trigger notifikasi awareness untuk Waka (Surat 2)
                $this->notificationService->notifyWakaForSurat2($tl);
                
            } catch (\Exception $e) {
                \Log::error("Error creating TindakLanjut + SuratPanggilan", [
                    'siswa_id' => $siswaId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'tipe_surat' => $tipeSurat,
                    'pembina_roles' => $pembinaRoles
                ]);
                
                // Re-throw untuk memastikan transaction rollback
                throw $e;
            }
        } else {
            // Update jika eskalasi diperlukan
            $this->eskalasiBilaPerluan($kasusAktif, $tipeSurat, $pemicu, $status, $pembinaRoles);
        }
    }

    /**
     * Rekonsiliasi tindak lanjut untuk seorang siswa berdasarkan riwayat saat ini.
     *
     * Jika setelah edit/hapus riwayat poin akumulasi turun sehingga tidak lagi memenuhi
     * threshold, maka kasus aktif akan dibatalkan. Jika masih memenuhi, akan dibuat
     * atau di-escalate sesuai kebutuhan.
     *
     * @param int $siswaId
     * @param bool $deleteIfNoSurat
     * @return void
     */
    public function reconcileForSiswa(int $siswaId, bool $deleteIfNoSurat = false): void
    {
        $siswa = Siswa::find($siswaId);
        if (!$siswa) return;

        // Ambil semua jenis pelanggaran yang pernah dicatat untuk siswa ini
        $jenisIds = RiwayatPelanggaran::where('siswa_id', $siswaId)
            ->pluck('jenis_pelanggaran_id')
            ->unique()
            ->toArray();

        $pelanggaranObjs = JenisPelanggaran::with('frequencyRules')
            ->whereIn('id', $jenisIds)
            ->get();

        $suratTypes = [];
        $pembinaRolesForSurat = [];

        // Re-evaluasi setiap pelanggaran
        foreach ($pelanggaranObjs as $pelanggaran) {
            if ($pelanggaran->usesFrequencyRules()) {
                $result = $this->evaluateFrequencyRules($siswaId, $pelanggaran);
                if ($result['surat_type']) {
                    $suratTypes[] = $result['surat_type'];
                    $pembinaRolesForSurat = $result['pembina_roles'] ?? [];
                }
            } else {
                // Backward compatibility
                if ($pelanggaran->poin >= 100) {
                    if ($pelanggaran->poin >= 200) {
                        $suratTypes[] = self::SURAT_3;
                    } elseif ($pelanggaran->poin > 500) {
                        $suratTypes[] = self::SURAT_4;
                    } else {
                        $suratTypes[] = self::SURAT_2;
                    }
                }
            }
        }

        $tipeSurat = $this->tentukanTipeSuratTertinggi($suratTypes);

        // Cari kasus aktif yang mungkin ada
        $kasusAktif = TindakLanjut::with('suratPanggilan')
            ->where('siswa_id', $siswaId)
            ->whereIn('status', ['Baru', 'Menunggu Persetujuan', 'Disetujui', 'Ditangani'])
            ->latest()
            ->first();

        if ($tipeSurat) {
            // Jika masih perlu tindak lanjut: buat baru atau update kasus yang ada
            if (!$kasusAktif) {
                // Tentukan status berdasarkan pembina
                $status = $this->tentukanStatusBerdasarkanPembina($pembinaRolesForSurat);
                $this->buatAtauUpdateTindakLanjut($siswaId, $tipeSurat, 'Rekonsiliasi', $status, $pembinaRolesForSurat);
                return;
            }

            // Jika ada kasus aktif, perbarui agar sesuai dengan tipe surat baru
            // Tentukan status berdasarkan pembina (bukan tipe surat)
            $statusBaru = $this->tentukanStatusBerdasarkanPembina($pembinaRolesForSurat);
            
            $kasusAktif->update([
                'pemicu' => 'Rekonsiliasi',
                'sanksi_deskripsi' => "Pemanggilan Wali Murid ({$tipeSurat})",
                'status' => $statusBaru,
            ]);

            if ($kasusAktif->suratPanggilan) {
                // Update pembina data
                $suratService = new SuratPanggilanService();
                $pembinaData = $suratService->buildPembinaData($pembinaRolesForSurat, $siswa);
                
                $kasusAktif->suratPanggilan()->update([
                    'tipe_surat' => $tipeSurat,
                    'pembina_data' => $pembinaData,
                    'keperluan' => 'Rekonsiliasi',
                ]);
            } else {
                // jika sebelumnya tidak ada surat, buat satu
                $suratService = new SuratPanggilanService();
                $pembinaData = $suratService->buildPembinaData($pembinaRolesForSurat, $siswa);
                $meetingSchedule = $suratService->setDefaultMeetingSchedule();

                $kasusAktif->suratPanggilan()->create([
                    'nomor_surat' => $suratService->generateNomorSurat(),
                    'tipe_surat' => $tipeSurat,
                    'tanggal_surat' => now(),
                    'pembina_data' => $pembinaData,
                    'tanggal_pertemuan' => $meetingSchedule['tanggal_pertemuan'],
                    'waktu_pertemuan' => $meetingSchedule['waktu_pertemuan'],
                    'keperluan' => 'Rekonsiliasi',
                ]);
            }

            return;
        }

        // Jika tidak ada tipe surat lagi namun ada kasus aktif
        if ($kasusAktif) {
            if ($deleteIfNoSurat) {
                // Hapus seluruh kasus (beserta suratnya) jika dipicu oleh penghapusan oleh pelapor
                $kasusAktif->delete();
                return;
            }

            // Jika tidak dihapus, tutup kasus secara rapi (set Selesai) dan hapus surat panggilan
            $kasusAktif->update([
                'status' => 'Selesai',
                'pemicu' => 'Dibatalkan otomatis setelah penyesuaian poin',
                'sanksi_deskripsi' => 'Dibatalkan oleh sistem',
            ]);

            if ($kasusAktif->suratPanggilan) {
                $kasusAktif->suratPanggilan()->delete();
            }
        }
    }

    /**
     * Update TindakLanjut jika diperlukan eskalasi ke level surat lebih tinggi.
     *
     * @param TindakLanjut $kasusAktif
     * @param string $tipeSuratBaru
     * @param string $pemicuBaru
     * @param string $statusBaru (DEPRECATED - akan dihitung ulang berdasarkan pembina)
     * @param array $pembinaRoles
     * @return void
     */
    private function eskalasiBilaPerluan(
        TindakLanjut $kasusAktif,
        string $tipeSuratBaru,
        string $pemicuBaru,
        string $statusBaru,
        array $pembinaRoles = []
    ): void {
        $existingTipe = $kasusAktif->suratPanggilan?->tipe_surat ?? '0';
        $levelLama = (int) filter_var($existingTipe, FILTER_SANITIZE_NUMBER_INT);
        $levelBaru = (int) filter_var($tipeSuratBaru, FILTER_SANITIZE_NUMBER_INT);

        if ($levelBaru > $levelLama) {
            // Tentukan status berdasarkan pembina (bukan parameter $statusBaru)
            $statusBaru = $this->tentukanStatusBerdasarkanPembina($pembinaRoles);
            
            $kasusAktif->update([
                'pemicu' => $pemicuBaru . ' (Eskalasi)',
                'sanksi_deskripsi' => "Pemanggilan Wali Murid ({$tipeSuratBaru})",
                'status' => $statusBaru,
            ]);

            if ($kasusAktif->suratPanggilan) {
                // Update pembina data untuk eskalasi
                $siswa = $kasusAktif->siswa;
                $suratService = new SuratPanggilanService();
                $pembinaData = $suratService->buildPembinaData($pembinaRoles, $siswa);

                $kasusAktif->suratPanggilan()->update([
                    'tipe_surat' => $tipeSuratBaru,
                    'pembina_data' => $pembinaData,
                    'keperluan' => $pemicuBaru,
                ]);
            }
        }
    }
}

