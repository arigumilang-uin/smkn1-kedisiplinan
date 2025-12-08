<?php

namespace App\Services\Pelanggaran;

use App\Data\Pelanggaran\RiwayatPelanggaranData;
use App\Data\Pelanggaran\RiwayatPelanggaranFilterData;
use App\Repositories\Contracts\RiwayatPelanggaranRepositoryInterface;
use App\Repositories\Contracts\JenisPelanggaranRepositoryInterface;
use App\Repositories\Contracts\SiswaRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Pelanggaran Service - The Orchestrator
 * 
 * Tanggung jawab:
 * - Koordinasi antara Repositories dan RulesEngine
 * - Handle database transactions
 * - Data persistence via repositories
 * - Delegasi business logic calculations ke RulesEngine
 * 
 * CRITICAL: Service ini TIDAK BOLEH menerima Request object.
 * Semua input harus berupa DTO atau primitive types.
 */
class PelanggaranService
{
    /**
     * PelanggaranService constructor.
     * 
     * Dependency injection: repositories dan rules engine.
     *
     * @param RiwayatPelanggaranRepositoryInterface $riwayatRepo
     * @param JenisPelanggaranRepositoryInterface $jenisRepo
     * @param SiswaRepositoryInterface $siswaRepo
     * @param PelanggaranRulesEngine $rulesEngine
     */
    public function __construct(
        private RiwayatPelanggaranRepositoryInterface $riwayatRepo,
        private JenisPelanggaranRepositoryInterface $jenisRepo,
        private SiswaRepositoryInterface $siswaRepo,
        private PelanggaranRulesEngine $rulesEngine
    ) {}

    /**
     * Catat pelanggaran baru.
     * 
     * ALUR:
     * 1. Simpan riwayat via repository (dalam transaction)
     * 2. Panggil RulesEngine untuk evaluasi dampak
     * 3. Return data hasil simpan
     *
     * @param RiwayatPelanggaranData $data
     * @return RiwayatPelanggaranData
     * @throws \Exception
     */
    public function catatPelanggaran(RiwayatPelanggaranData $data): RiwayatPelanggaranData
    {
        DB::beginTransaction();

        try {
            // Siapkan data untuk disimpan
            $riwayatArray = [
                'siswa_id' => $data->siswa_id,
                'jenis_pelanggaran_id' => $data->jenis_pelanggaran_id,
                'guru_pencatat_user_id' => $data->guru_pencatat_user_id,
                'tanggal_kejadian' => $data->tanggal_kejadian,
                'keterangan' => $data->keterangan,
                'bukti_foto_path' => $data->bukti_foto_path,
            ];

            // Simpan riwayat pelanggaran via repository
            $createdRiwayat = $this->riwayatRepo->create($riwayatArray);

            // Evaluasi dampak pelanggaran menggunakan Rules Engine
            // RulesEngine akan:
            // - Cek frekuensi pelanggaran
            // - Hitung total poin
            // - Tentukan apakah perlu buat tindak lanjut
            // - Generate surat panggilan jika perlu
            $this->rulesEngine->processBatch(
                $data->siswa_id,
                [$data->jenis_pelanggaran_id]
            );

            DB::commit();

            return RiwayatPelanggaranData::from($createdRiwayat);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update riwayat pelanggaran.
     * 
     * ALUR:
     * 1. Update data via repository (dalam transaction)
     * 2. Reconcile tindak lanjut siswa (karena poin/frekuensi berubah)
     * 3. Return data hasil update
     *
     * @param int $id
     * @param RiwayatPelanggaranData $data
     * @param string|null $oldBuktiFotoPath Path lama untuk dihapus jika ada upload baru
     * @return RiwayatPelanggaranData
     * @throws \Exception
     */
    public function updatePelanggaran(
        int $id,
        RiwayatPelanggaranData $data,
        ?string $oldBuktiFotoPath = null
    ): RiwayatPelanggaranData {
        DB::beginTransaction();

        try {
            // Siapkan data update
            $updateArray = [
                'jenis_pelanggaran_id' => $data->jenis_pelanggaran_id,
                'tanggal_kejadian' => $data->tanggal_kejadian,
                'keterangan' => $data->keterangan,
            ];

            // Handle file upload jika ada
            if ($data->bukti_foto_path) {
                // Hapus file lama jika ada
                if ($oldBuktiFotoPath) {
                    Storage::disk('public')->delete($oldBuktiFotoPath);
                }
                $updateArray['bukti_foto_path'] = $data->bukti_foto_path;
            }

            // Update via repository
            $updatedRiwayat = $this->riwayatRepo->update($id, $updateArray);

            // Reconcile tindak lanjut karena data berubah
            // RulesEngine akan re-evaluasi semua pelanggaran siswa ini
            $this->rulesEngine->reconcileForSiswa($data->siswa_id, false);

            DB::commit();

            return RiwayatPelanggaranData::from($updatedRiwayat);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Hapus riwayat pelanggaran.
     * 
     * ALUR:
     * 1. Hapus file bukti foto jika ada
     * 2. Hapus record via repository
     * 3. Reconcile tindak lanjut (poin/frekuensi berkurang)
     *
     * @param int $id
     * @param int $siswaId Siswa ID untuk reconcile
     * @param string|null $buktiFotoPath Path file untuk dihapus
     * @return bool
     */
    public function deletePelanggaran(int $id, int $siswaId, ?string $buktiFotoPath = null): bool
    {
        // Hapus file bukti foto jika ada
        if ($buktiFotoPath) {
            Storage::disk('public')->delete($buktiFotoPath);
        }

        // Hapus record via repository
        $deleted = $this->riwayatRepo->delete($id);

        if ($deleted) {
            // Reconcile dengan flag deleteIfNoSurat = true
            // Jika setelah hapus tidak ada yang trigger surat, kasus akan dihapus
            $this->rulesEngine->reconcileForSiswa($siswaId, true);
        }

        return $deleted;
    }

    /**
     * Dapatkan riwayat pelanggaran dengan filter dan pagination.
     * 
     * CRITICAL: Method ini menerima RiwayatPelanggaranFilterData (DTO),
     * BUKAN Request object.
     *
     * @param RiwayatPelanggaranFilterData $filters
     * @return LengthAwarePaginator
     */
    public function getFilteredRiwayat(RiwayatPelanggaranFilterData $filters): LengthAwarePaginator
    {
        return $this->riwayatRepo->filterAndPaginate($filters);
    }

    /**
     * Hitung total poin pelanggaran siswa.
     * 
     * Delegasi ke RulesEngine untuk calculation.
     *
     * @param int $siswaId
     * @return int
     */
    public function calculateTotalPoin(int $siswaId): int
    {
        return $this->rulesEngine->hitungTotalPoinAkumulasi($siswaId);
    }

    /**
     * Cek frekuensi pelanggaran tertentu untuk siswa.
     * 
     * Delegasi ke repository untuk counting.
     *
     * @param int $siswaId
     * @param int $jenisPelanggaranId
     * @return int
     */
    public function checkFrequency(int $siswaId, int $jenisPelanggaranId): int
    {
        return $this->riwayatRepo->countBySiswaAndJenis($siswaId, $jenisPelanggaranId);
    }

    /**
     * Dapatkan riwayat pelanggaran siswa dengan relasi lengkap.
     *
     * @param int $siswaId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRiwayatBySiswa(int $siswaId)
    {
        return $this->riwayatRepo->findBySiswa($siswaId);
    }

    /**
     * Dapatkan recent violations untuk siswa.
     *
     * @param int $siswaId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentViolations(int $siswaId, int $limit = 10)
    {
        return $this->riwayatRepo->getRecentBySiswa($siswaId, $limit);
    }

    /**
     * Dapatkan statistik pelanggaran untuk siswa.
     * 
     * CONTOH USE CASE: Dashboard siswa, profil siswa
     *
     * @param int $siswaId
     * @return array{
     *     total_poin: int,
     *     total_violations: int,
     *     recent_violations: \Illuminate\Database\Eloquent\Collection,
     *     pembinaan_rekomendasi: array
     * }
     */
    public function getStatistikSiswa(int $siswaId): array
    {
        $totalPoin = $this->calculateTotalPoin($siswaId);
        $riwayat = $this->riwayatRepo->findBySiswa($siswaId);
        $recentViolations = $this->riwayatRepo->getRecentBySiswa($siswaId, 5);
        
        // Dapatkan rekomendasi pembinaan dari RulesEngine
        $pembinaanRekomendasi = $this->rulesEngine->getPembinaanInternalRekomendasi($totalPoin);

        return [
            'total_poin' => $totalPoin,
            'total_violations' => $riwayat->count(),
            'recent_violations' => $recentViolations,
            'pembinaan_rekomendasi' => $pembinaanRekomendasi,
        ];
    }

    /**
     * Dapatkan semua jenis pelanggaran aktif.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveJenisPelanggaran()
    {
        return $this->jenisRepo->getActive();
    }

    /**
     * Dapatkan jenis pelanggaran by filter category.
     *
     * @param string $filterCategory
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getJenisByCategory(string $filterCategory)
    {
        return $this->jenisRepo->getByFilterCategory($filterCategory);
    }
}
