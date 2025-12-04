<?php

namespace App\Services;

use App\Models\JenisPelanggaran;
use App\Models\RiwayatPelanggaran;
use App\Models\Siswa;
use App\Models\TindakLanjut;
use Carbon\Carbon;

/**
 * Service untuk Rules Engine Pelanggaran
 *
 * Tanggung jawab:
 * - Mengevaluasi poin dan frekuensi pelanggaran siswa
 * - Menentukan jenis surat (tipe eskalasi)
 * - Membuat/update TindakLanjut dan SuratPanggilan otomatis
 */
class PelanggaranRulesEngine
{
    /**
     * Konstanta tipe surat (eskalasi levels)
     */
    const SURAT_1 = 'Surat 1';
    const SURAT_2 = 'Surat 2';
    const SURAT_3 = 'Surat 3';

    /**
     * Konstanta threshold poin
     */
    const THRESHOLD_SURAT_2_MIN = 100;
    const THRESHOLD_SURAT_2_MAX = 500;
    const THRESHOLD_SURAT_3_MIN = 501;
    const THRESHOLD_AKUMULASI_SEDANG_MIN = 55;
    const THRESHOLD_AKUMULASI_SEDANG_MAX = 300;
    const THRESHOLD_AKUMULASI_KRITIS = 301;

    /**
     * Konstanta frekuensi spesifik
     */
    const FREKUENSI_ATRIBUT = 10;
    const FREKUENSI_ALFA = 4;

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

        $pelanggaranObjs = JenisPelanggaran::whereIn('id', $pelanggaranIds)->get();
        if ($pelanggaranObjs->isEmpty()) return;

        // Hitung poin dan tentukan tipe surat
        $totalPoinAkumulasi = $this->hitungTotalPoinAkumulasi($siswaId);
        $poinBaruTotal = $pelanggaranObjs->sum('poin');
        
        // Tentukan tipe surat dan pemicu
        [$tipeSurat, $pemicu, $status] = $this->tentukanTipeSuratDanStatus(
            $siswaId,
            $pelanggaranObjs,
            $poinBaruTotal,
            $totalPoinAkumulasi
        );

        // Jika ada surat yang perlu dibuat, create/update TindakLanjut
        if ($tipeSurat) {
            $this->buatAtauUpdateTindakLanjut($siswaId, $tipeSurat, $pemicu, $status);
        }
    }

    /**
     * Hitung total poin akumulasi siswa dari semua riwayat pelanggaran.
     *
     * @param int $siswaId
     * @return int
     */
    private function hitungTotalPoinAkumulasi(int $siswaId): int
    {
        return RiwayatPelanggaran::where('siswa_id', $siswaId)
            ->join('jenis_pelanggaran', 'riwayat_pelanggaran.jenis_pelanggaran_id', '=', 'jenis_pelanggaran.id')
            ->sum('jenis_pelanggaran.poin');
    }

    /**
     * Tentukan jenis surat (tipe eskalasi) berdasarkan frekuensi dan poin.
     *
     * @param int $siswaId
     * @param \Illuminate\Database\Eloquent\Collection $pelanggaranObjs
     * @param int $poinBaruTotal Poin total dari pelanggaran baru dalam batch ini
     * @param int $totalPoinAkumulasi Total poin akumulatif termasuk yang baru
     * @return array [$tipeSurat, $pemicu, $status]
     */
    private function tentukanTipeSuratDanStatus(
        int $siswaId,
        $pelanggaranObjs,
        int $poinBaruTotal,
        int $totalPoinAkumulasi
    ): array {
        $tipeSurat = null;
        $pemicu = null;
        $status = 'Baru';

        // 1. Cek frekuensi spesifik (atribut, alfa)
        foreach ($pelanggaranObjs as $pel) {
            $hasil = $this->cekFrekuensiSpesifik($siswaId, $pel);
            if ($hasil) {
                [$tipeSurat, $pemicu] = $hasil;
                break;
            }
        }

        // 2. Jika belum ditentukan frekuensi, gunakan threshold poin
        if (!$tipeSurat) {
            [$tipeSurat, $pemicu, $status] = $this->tentukanBerdasarkanPoin($poinBaruTotal);
        }

        // 3. Akumulasi poin: jika total sudah besar, bisa eskalasi
        if ($totalPoinAkumulasi >= self::THRESHOLD_AKUMULASI_SEDANG_MIN) {
            if ($totalPoinAkumulasi <= self::THRESHOLD_AKUMULASI_SEDANG_MAX) {
                if (!$tipeSurat || $tipeSurat === self::SURAT_1) {
                    $tipeSurat = self::SURAT_2;
                    $pemicu = "Akumulasi {$totalPoinAkumulasi}";
                }
            } elseif ($totalPoinAkumulasi >= self::THRESHOLD_AKUMULASI_KRITIS) {
                $tipeSurat = self::SURAT_3;
                $status = 'Menunggu Persetujuan';
                $pemicu = "Akumulasi Kritis {$totalPoinAkumulasi}";
            }
        }

        return [$tipeSurat, $pemicu, $status];
    }

    /**
     * Cek frekuensi spesifik untuk pelanggaran (atribut, alfa).
     *
     * @param int $siswaId
     * @param JenisPelanggaran $pelanggaran
     * @return array|null [$tipeSurat, $pemicu] atau null
     */
    private function cekFrekuensiSpesifik(int $siswaId, JenisPelanggaran $pelanggaran): ?array
    {
        $namaLower = strtolower($pelanggaran->nama_pelanggaran);
        $frekuensi = RiwayatPelanggaran::where('siswa_id', $siswaId)
            ->where('jenis_pelanggaran_id', $pelanggaran->id)
            ->count();

        if (str_contains($namaLower, 'atribut') && $frekuensi >= self::FREKUENSI_ATRIBUT) {
            return [self::SURAT_1, "Atribut ({$frekuensi}x)"];
        }

        if (str_contains($namaLower, 'alfa') && $frekuensi >= self::FREKUENSI_ALFA) {
            return [self::SURAT_1, "Alfa ({$frekuensi}x)"];
        }

        return null;
    }

    /**
     * Tentukan tipe surat berdasarkan poin.
     *
     * @param int $poinTotal
     * @return array [$tipeSurat, $pemicu, $status]
     */
    private function tentukanBerdasarkanPoin(int $poinTotal): array
    {
        if ($poinTotal >= self::THRESHOLD_SURAT_2_MIN && $poinTotal <= self::THRESHOLD_SURAT_2_MAX) {
            return [self::SURAT_2, "Pelanggaran Berat", 'Baru'];
        }

        if ($poinTotal > self::THRESHOLD_SURAT_3_MIN) {
            return [self::SURAT_3, "Sangat Berat", 'Menunggu Persetujuan'];
        }

        return [null, null, 'Baru'];
    }

    /**
     * Buat atau update TindakLanjut dan SuratPanggilan untuk siswa.
     *
     * @param int $siswaId
     * @param string $tipeSurat
     * @param string $pemicu
     * @param string $status
     * @return void
     */
    private function buatAtauUpdateTindakLanjut(
        int $siswaId,
        string $tipeSurat,
        string $pemicu,
        string $status
    ): void {
        $sanksi = "Pemanggilan Wali Murid ({$tipeSurat})";

        // Cari kasus aktif siswa
        $kasusAktif = TindakLanjut::with('suratPanggilan')
            ->where('siswa_id', $siswaId)
            ->whereIn('status', ['Baru', 'Menunggu Persetujuan', 'Disetujui', 'Ditangani'])
            ->latest()
            ->first();

        if (!$kasusAktif) {
            // Buat TindakLanjut baru
            $tl = TindakLanjut::create([
                'siswa_id' => $siswaId,
                'pemicu' => $pemicu,
                'sanksi_deskripsi' => $sanksi,
                'status' => $status,
            ]);
            
            // Buat SuratPanggilan
            $tl->suratPanggilan()->create([
                'nomor_surat' => 'DRAFT/' . rand(100, 999),
                'tipe_surat' => $tipeSurat,
                'tanggal_surat' => now(),
            ]);
        } else {
            // Update jika eskalasi diperlukan
            $this->eskalasiBilaPerluan($kasusAktif, $tipeSurat, $pemicu, $status);
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

        $pelanggaranObjs = JenisPelanggaran::whereIn('id', $jenisIds)->get();

        $totalPoinAkumulasi = $this->hitungTotalPoinAkumulasi($siswaId);

        // Untuk rekalkulasi gunakan total akumulasi sebagai dasar penentuan tipe surat
        [$tipeSurat, $pemicu, $status] = $this->tentukanTipeSuratDanStatus(
            $siswaId,
            $pelanggaranObjs,
            $totalPoinAkumulasi,
            $totalPoinAkumulasi
        );

        // Cari kasus aktif yang mungkin ada
        $kasusAktif = TindakLanjut::with('suratPanggilan')
            ->where('siswa_id', $siswaId)
            ->whereIn('status', ['Baru', 'Menunggu Persetujuan', 'Disetujui', 'Ditangani'])
            ->latest()
            ->first();

        if ($tipeSurat) {
            // Jika masih perlu tindak lanjut: buat baru atau update kasusu yang ada
            if (!$kasusAktif) {
                $this->buatAtauUpdateTindakLanjut($siswaId, $tipeSurat, $pemicu, $status);
                return;
            }

            // Jika ada kasus aktif, perbarui agar sesuai dengan tipe surat baru
            $kasusAktif->update([
                'pemicu' => $pemicu,
                'sanksi_deskripsi' => "Pemanggilan Wali Murid ({$tipeSurat})",
                'status' => $status,
            ]);

            if ($kasusAktif->suratPanggilan) {
                $kasusAktif->suratPanggilan()->update(['tipe_surat' => $tipeSurat]);
            } else {
                // jika sebelumnya tidak ada surat, buat satu
                $kasusAktif->suratPanggilan()->create([
                    'nomor_surat' => 'DRAFT/' . rand(100, 999),
                    'tipe_surat' => $tipeSurat,
                    'tanggal_surat' => now(),
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
     * @param string $statusBaru
     * @return void
     */
    private function eskalasiBilaPerluan(
        TindakLanjut $kasusAktif,
        string $tipeSuratBaru,
        string $pemicuBaru,
        string $statusBaru
    ): void {
        $existingTipe = $kasusAktif->suratPanggilan?->tipe_surat ?? '0';
        $levelLama = (int) filter_var($existingTipe, FILTER_SANITIZE_NUMBER_INT);
        $levelBaru = (int) filter_var($tipeSuratBaru, FILTER_SANITIZE_NUMBER_INT);

        if ($levelBaru > $levelLama) {
            $kasusAktif->update([
                'pemicu' => $pemicuBaru . ' (Eskalasi)',
                'sanksi_deskripsi' => "Pemanggilan Wali Murid ({$tipeSuratBaru})",
                'status' => $statusBaru,
            ]);

            if ($kasusAktif->suratPanggilan) {
                $kasusAktif->suratPanggilan()->update(['tipe_surat' => $tipeSuratBaru]);
            }
        }
    }
}
