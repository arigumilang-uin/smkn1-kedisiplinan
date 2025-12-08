<?php

namespace App\Repositories;

use App\Models\RiwayatPelanggaran;
use App\Repositories\Contracts\RiwayatPelanggaranRepositoryInterface;
use App\Data\Pelanggaran\RiwayatPelanggaranFilterData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Riwayat Pelanggaran Repository Implementation
 * 
 * Handles all data access operations for RiwayatPelanggaran entity.
 * Implements RiwayatPelanggaranRepositoryInterface and extends BaseRepository.
 */
class RiwayatPelanggaranRepository extends BaseRepository implements RiwayatPelanggaranRepositoryInterface
{
    /**
     * RiwayatPelanggaranRepository constructor.
     *
     * @param RiwayatPelanggaran $model
     */
    public function __construct(RiwayatPelanggaran $model)
    {
        parent::__construct($model);
    }

    /**
     * Find riwayat pelanggaran by siswa.
     *
     * @param int $siswaId
     * @return Collection
     */
    public function findBySiswa(int $siswaId): Collection
    {
        return $this->model
            ->where('siswa_id', $siswaId)
            ->with(['siswa', 'jenisPelanggaran.kategoriPelanggaran', 'guruPencatat'])
            ->orderBy('tanggal_kejadian', 'desc')
            ->get();
    }

    /**
     * Find riwayat pelanggaran by jenis pelanggaran.
     *
     * @param int $jenisPelanggaranId
     * @return Collection
     */
    public function findByJenisPelanggaran(int $jenisPelanggaranId): Collection
    {
        return $this->model
            ->where('jenis_pelanggaran_id', $jenisPelanggaranId)
            ->with(['siswa.kelas', 'jenisPelanggaran', 'guruPencatat'])
            ->orderBy('tanggal_kejadian', 'desc')
            ->get();
    }

    /**
     * Find riwayat pelanggaran by guru pencatat.
     *
     * @param int $guruId
     * @return Collection
     */
    public function findByGuruPencatat(int $guruId): Collection
    {
        return $this->model
            ->where('guru_pencatat_user_id', $guruId)
            ->with(['siswa.kelas', 'jenisPelanggaran', 'guruPencatat'])
            ->orderBy('tanggal_kejadian', 'desc')
            ->get();
    }

    /**
     * Find riwayat pelanggaran within date range.
     *
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function findByDateRange(string $startDate, string $endDate): Collection
    {
        return $this->model
            ->whereDate('tanggal_kejadian', '>=', $startDate)
            ->whereDate('tanggal_kejadian', '<=', $endDate)
            ->with(['siswa.kelas', 'jenisPelanggaran', 'guruPencatat'])
            ->orderBy('tanggal_kejadian', 'desc')
            ->get();
    }

    /**
     * Count riwayat pelanggaran by siswa and jenis pelanggaran.
     *
     * @param int $siswaId
     * @param int $jenisPelanggaranId
     * @return int
     */
    public function countBySiswaAndJenis(int $siswaId, int $jenisPelanggaranId): int
    {
        return $this->model
            ->where('siswa_id', $siswaId)
            ->where('jenis_pelanggaran_id', $jenisPelanggaranId)
            ->count();
    }

    /**
     * Get total poin pelanggaran for a siswa.
     * 
     * STRATEGI OPTIMASI:
     * Menggunakan JOIN + DB aggregation (SUM) untuk performa optimal.
     * Database engine yang handle aggregation, tidak perlu load semua record ke memory.
     * 
     * Query yang dihasilkan:
     * SELECT SUM(jenis_pelanggaran.poin)
     * FROM riwayat_pelanggaran
     * JOIN jenis_pelanggaran ON riwayat_pelanggaran.jenis_pelanggaran_id = jenis_pelanggaran.id
     * WHERE riwayat_pelanggaran.siswa_id = ?
     *
     * @param int $siswaId
     * @return int
     */
    public function getTotalPoinBySiswa(int $siswaId): int
    {
        $total = $this->model
            ->where('riwayat_pelanggaran.siswa_id', $siswaId)
            ->join('jenis_pelanggaran', 'riwayat_pelanggaran.jenis_pelanggaran_id', '=', 'jenis_pelanggaran.id')
            ->sum('jenis_pelanggaran.poin');

        return (int) ($total ?? 0);
    }

    /**
     * Filter and paginate riwayat pelanggaran based on filter criteria.
     * 
     * CRITICAL: Accepts RiwayatPelanggaranFilterData (DTO), NOT Request object.
     * This ensures clean separation between HTTP layer and repository layer.
     *
     * @param RiwayatPelanggaranFilterData $filters
     * @return LengthAwarePaginator
     */
    public function filterAndPaginate(RiwayatPelanggaranFilterData $filters): LengthAwarePaginator
    {
        // Start building query dengan eager loading untuk prevent N+1 queries
        $query = $this->model
            ->newQuery()
            ->with(['siswa.kelas.jurusan', 'jenisPelanggaran.kategoriPelanggaran', 'guruPencatat']);

        // Apply filter by siswa
        if ($filters->siswa_id) {
            $query->where('siswa_id', $filters->siswa_id);
        }

        // Apply filter by jenis pelanggaran
        if ($filters->jenis_pelanggaran_id) {
            $query->where('jenis_pelanggaran_id', $filters->jenis_pelanggaran_id);
        }

        // Apply filter by guru pencatat
        if ($filters->guru_pencatat_user_id) {
            $query->where('guru_pencatat_user_id', $filters->guru_pencatat_user_id);
        }

        // Apply filter by kelas (via siswa relationship)
        if ($filters->kelas_id) {
            $query->whereHas('siswa', function ($q) use ($filters) {
                $q->where('kelas_id', $filters->kelas_id);
            });
        }

        // Apply filter by jurusan (via siswa.kelas relationship)
        if ($filters->jurusan_id) {
            $query->whereHas('siswa.kelas', function ($q) use ($filters) {
                $q->where('jurusan_id', $filters->jurusan_id);
            });
        }

        // Apply filter by tingkat (via jenis pelanggaran kategori)
        if ($filters->tingkat) {
            $query->whereHas('jenisPelanggaran.kategoriPelanggaran', function ($q) use ($filters) {
                $q->where('nama_kategori', $filters->tingkat->value);
            });
        }

        // Apply date range filter
        if ($filters->tanggal_dari) {
            $query->whereDate('tanggal_kejadian', '>=', $filters->tanggal_dari);
        }

        if ($filters->tanggal_sampai) {
            $query->whereDate('tanggal_kejadian', '<=', $filters->tanggal_sampai);
        }

        // Apply search filter (nama siswa or NISN)
        if ($filters->search) {
            $query->whereHas('siswa', function ($q) use ($filters) {
                $q->where('nama_siswa', 'like', "%{$filters->search}%")
                    ->orWhere('nisn', 'like', "%{$filters->search}%");
            });
        }

        // Apply sorting
        $sortBy = $filters->sortBy ?: 'tanggal_kejadian';
        $sortDirection = $filters->getSortDirection();
        $query->orderBy($sortBy, $sortDirection);

        // Return paginated results
        return $query->paginate($filters->perPage);
    }

    /**
     * Get recent violations for a siswa.
     *
     * @param int $siswaId
     * @param int $limit
     * @return Collection
     */
    public function getRecentBySiswa(int $siswaId, int $limit = 10): Collection
    {
        return $this->model
            ->where('siswa_id', $siswaId)
            ->with(['jenisPelanggaran.kategoriPelanggaran', 'guruPencatat'])
            ->orderBy('tanggal_kejadian', 'desc')
            ->limit($limit)
            ->get();
    }
}
