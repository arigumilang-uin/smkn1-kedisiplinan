<?php

namespace App\Services\Siswa;

use App\Data\Siswa\SiswaData;
use App\Data\Siswa\SiswaFilterData;
use App\Data\User\UserData;
use App\Repositories\Contracts\SiswaRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\User\UserNamingService;
use App\Models\Role;
use App\Exceptions\BusinessValidationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Siswa Service
 * 
 * Service layer untuk business logic terkait Siswa.
 * CRITICAL: Service ini TIDAK BOLEH menerima Request object.
 * Semua input harus berupa DTO atau primitive types.
 */
class SiswaService
{
    /**
     * SiswaService constructor.
     * 
     * Dependency injection: repositories dan helper services.
     *
     * @param SiswaRepositoryInterface $siswaRepository
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(
        private SiswaRepositoryInterface $siswaRepository,
        private UserRepositoryInterface $userRepository,
        private \App\Services\Pelanggaran\PelanggaranService $pelanggaranService
    ) {}

    /**
     * Buat siswa baru dengan opsi pembuatan akun wali murid otomatis.
     * 
     * LOGIKA BISNIS (dipindahkan dari controller lama):
     * 1. Jika create_wali = true dan belum ada wali_murid_user_id:
     *    - Generate username dan password untuk wali murid
     *    - Buat user baru dengan role Wali Murid
     *    - Set wali_murid_user_id ke siswa
     * 2. Simpan siswa ke database
     * 3. Return kredensial wali jika dibuat (untuk ditampilkan ke operator)
     *
     * @param SiswaData $siswaData
     * @param bool $createWali Apakah ingin membuat akun wali murid otomatis
     * @return array{siswa: SiswaData, wali_credentials: array|null}
     * @throws BusinessValidationException
     */
    public function createSiswa(SiswaData $siswaData, bool $createWali = false): array
    {
        DB::beginTransaction();
        
        try {
            $waliCredentials = null;
            $waliMuridUserId = $siswaData->wali_murid_user_id;

            // Logika bisnis: Buat akun wali murid jika diminta dan belum ada
            if ($createWali && !$waliMuridUserId) {
                $waliCredentials = $this->createWaliMuridAccount(
                    $siswaData->nisn,
                    $siswaData->nama_siswa
                );
                
                $waliMuridUserId = $waliCredentials['user_id'];
            }

            // Siapkan data siswa untuk disimpan
            $siswaArray = [
                'kelas_id' => $siswaData->kelas_id,
                'wali_murid_user_id' => $waliMuridUserId,
                'nisn' => $siswaData->nisn,
                'nama_siswa' => $siswaData->nama_siswa,
                'nomor_hp_wali_murid' => $siswaData->nomor_hp_wali_murid,
            ];

            // Simpan siswa via repository
            $createdSiswa = $this->siswaRepository->create($siswaArray);

            DB::commit();

            return [
                'siswa' => SiswaData::from($createdSiswa),
                'wali_credentials' => $waliCredentials,
            ];

        } catch (BusinessValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new BusinessValidationException(
                'Gagal membuat data siswa: ' . $e->getMessage()
            );
        }
    }

    /**
     * Update data siswa.
     * 
     * LOGIKA BISNIS:
     * - Operator: bisa update semua field
     * - Wali Kelas: hanya bisa update nomor_hp_wali_murid
     * - Validasi akses dilakukan di controller/authorization layer
     *
     * @param int $siswaId
     * @param SiswaData $siswaData
     * @param bool $isWaliKelas Apakah user yang update adalah Wali Kelas
     * @return SiswaData
     * @throws \Exception
     */
    public function updateSiswa(int $siswaId, SiswaData $siswaData, bool $isWaliKelas = false): SiswaData
    {
        $updateData = [];

        if ($isWaliKelas) {
            // Wali Kelas hanya bisa update nomor HP wali murid
            $updateData = [
                'nomor_hp_wali_murid' => $siswaData->nomor_hp_wali_murid,
            ];
        } else {
            // Operator bisa update semua field
            $updateData = [
                'kelas_id' => $siswaData->kelas_id,
                'wali_murid_user_id' => $siswaData->wali_murid_user_id,
                'nisn' => $siswaData->nisn,
                'nama_siswa' => $siswaData->nama_siswa,
                'nomor_hp_wali_murid' => $siswaData->nomor_hp_wali_murid,
            ];
        }

        $updatedSiswa = $this->siswaRepository->update($siswaId, $updateData);
        
        return SiswaData::from($updatedSiswa);
    }

    /**
     * Hapus siswa berdasarkan ID.
     *
     * @param int $siswaId
     * @return bool
     */
    public function deleteSiswa(int $siswaId): bool
    {
        return $this->siswaRepository->delete($siswaId);
    }

    /**
     * Ambil data siswa berdasarkan ID.
     *
     * @param int $siswaId
     * @return SiswaData|null
     */
    public function findSiswa(int $siswaId): ?SiswaData
    {
        $siswa = $this->siswaRepository->find($siswaId);
        return $siswa ? SiswaData::from($siswa) : null;
    }

    /**
     * Ambil data siswa berdasarkan NISN.
     *
     * @param string $nisn
     * @return SiswaData|null
     */
    public function findByNisn(string $nisn): ?SiswaData
    {
        return $this->siswaRepository->findByNisn($nisn);
    }

    /**
     * Dapatkan daftar siswa dengan filter dan pagination.
     * 
     * CRITICAL: Method ini menerima SiswaFilterData (DTO), BUKAN Request object.
     * Ini menjaga service tetap HTTP-agnostic dan testable.
     *
     * @param SiswaFilterData $filters
     * @return LengthAwarePaginator
     */
    public function getFilteredSiswa(SiswaFilterData $filters): LengthAwarePaginator
    {
        return $this->siswaRepository->filterAndPaginate($filters);
    }

    /**
     * Dapatkan semua siswa dalam kelas tertentu.
     *
     * @param int $kelasId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSiswaByKelas(int $kelasId)
    {
        return $this->siswaRepository->findByKelas($kelasId);
    }

    /**
     * Dapatkan semua siswa dalam jurusan tertentu.
     *
     * @param int $jurusanId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSiswaByJurusan(int $jurusanId)
    {
        return $this->siswaRepository->findByJurusan($jurusanId);
    }

    /**
     * Dapatkan semua siswa dengan wali murid tertentu.
     *
     * @param int $waliMuridId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSiswaByWaliMurid(int $waliMuridId)
    {
        return $this->siswaRepository->findByWaliMurid($waliMuridId);
    }

    /**
     * Dapatkan detail lengkap siswa untuk halaman show.
     * 
     * LOGIKA BISNIS:
     * - Eager load semua relationships yang dibutuhkan
     * - Hitung total poin pelanggaran
     * - Return array dengan siswa model dan total poin
     *
     * @param int $siswaId
     * @return array{siswa: \App\Models\Siswa, totalPoin: int}
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getSiswaDetail(int $siswaId): array
    {
        // Eager load semua relationships untuk menghindari N+1 queries
        $siswa = \App\Models\Siswa::with([
            'kelas.jurusan.kaprodi',
            'kelas.waliKelas',
            'waliMurid',
            'riwayatPelanggaran.jenisPelanggaran.kategoriPelanggaran',
            'riwayatPelanggaran.guruPencatat',
            'tindakLanjut'
        ])->findOrFail($siswaId);

        // BUSINESS LOGIC: Hitung total poin menggunakan PelanggaranService
        // Service akan delegate ke RulesEngine yang support frequency-based rules
        $totalPoin = $this->pelanggaranService->calculateTotalPoin($siswaId);

        // Get pembinaan internal recommendation
        $pembinaanRekomendasi = $this->pelanggaranService->getStatistikSiswa($siswaId)['pembinaan_rekomendasi'] ?? [
            'pembina_roles' => [],
            'keterangan' => '',
            'range_text' => '',
        ];

        return [
            'siswa' => $siswa,
            'totalPoin' => $totalPoin,
            'pembinaanRekomendasi' => $pembinaanRekomendasi,
        ];
    }

    /**
     * Dapatkan data siswa untuk form edit.
     * 
     * Method ini mengambil siswa tanpa eager loading berlebihan,
     * karena form edit hanya butuh data siswa itu sendiri.
     *
     * @param int $siswaId
     * @return \App\Models\Siswa
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getSiswaForEdit(int $siswaId)
    {
        return \App\Models\Siswa::findOrFail($siswaId);
    }

    /**
     * Dapatkan semua jurusan untuk dropdown filter.
     * 
     * Method ini menyediakan master data untuk UI.
     * Sorted by nama_jurusan untuk UX yang lebih baik.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllJurusanForFilter()
    {
        return \App\Models\Jurusan::orderBy('nama_jurusan')->get();
    }

    /**
     * Dapatkan semua kelas untuk dropdown filter.
     * 
     * Method ini menyediakan master data untuk UI.
     * Sorted by nama_kelas untuk UX yang lebih baik.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllKelasForFilter()
    {
        return \App\Models\Kelas::orderBy('nama_kelas')->get();
    }

    /**
     * Dapatkan semua kelas untuk form create/edit.
     * 
     * PERFORMANCE OPTIMIZED: Returns only necessary columns
     * 
     * @return \Illuminate\Support\Collection<stdClass>
     */
    public function getAllKelas()
    {
        // OPTIMIZATION: Get only id, nama_kelas, jurusan_id for dropdown
        return \Illuminate\Support\Facades\DB::table('kelas')
            ->leftJoin('jurusan', 'kelas.jurusan_id', '=', 'jurusan.id')
            ->select('kelas.id', 'kelas.nama_kelas', 'kelas.jurusan_id', 'jurusan.nama_jurusan')
            ->orderBy('kelas.nama_kelas')
            ->get();
    }

    /**
     * Dapatkan semua wali murid yang tersedia untuk form create/edit.
     * 
     * PERFORMANCE OPTIMIZED: Uses DB query instead of Eloquent Models
     * Returns only id, nama, username, email (lightweight stdClass), not full User Models
     * 
     * BEFORE: 205 User Models × 20KB = ~4MB
     * AFTER: 205 stdClass × 0.5KB = ~100KB (40x lighter!)
     * 
     * UPDATED: Include Developer role for testing purposes
     *
     * @return \Illuminate\Support\Collection<stdClass>
     */
    public function getAvailableWaliMurid()
    {
        // OPTIMIZATION: Direct DB query with minimal columns
        return \Illuminate\Support\Facades\DB::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->whereIn('roles.nama_role', ['Wali Murid', 'Developer']) // Allow Developer for testing
            ->select('users.id', 'users.nama', 'users.username', 'users.email')
            ->orderBy('users.nama')
            ->get();
        
        // Returns Collection<stdClass>, NOT Collection<User>!
        // Each object: {id: 1, nama: "Budi", username: "budi123", email: "budi@..."}
    }

    /**
     * Helper method: Buat akun wali murid baru.
     * 
     * LOGIKA (dari controller lama baris 136-167):
     * - Username: wali.{nisn} (dengan pengecekan uniqueness)
     * - Password: smkn1.walimurid.{nisn}
     * - Email: {username}@no-reply.local
     * - Nama: Wali dari {nama_siswa}
     *
     * @param string $nisn
     * @param string $namaSiswa
     * @return array{user_id: int, username: string, password: string}
     * @throws \Exception
     */
    private function createWaliMuridAccount(string $nisn, string $namaSiswa): array
    {
        // Bersihkan NISN dari karakter non-digit
        $nisnClean = preg_replace('/\D+/', '', $nisn);
        
        if ($nisnClean === '') {
            // Fallback: gunakan slug dari nama siswa
            $nisnClean = Str::slug($namaSiswa);
        }

        // Generate username dengan pengecekan uniqueness
        $baseUsername = 'wali.' . $nisnClean;
        $username = $baseUsername;
        $counter = 1;
        
        while ($this->userRepository->usernameExists($username)) {
            $counter++;
            $username = $baseUsername . $counter;
        }

        // Generate password standardized
        $password = 'smkn1.walimurid.' . $nisnClean;
        
        // Generate email dummy
        $email = $username . '@no-reply.local';
        
        // Generate nama
        $nama = 'Wali dari ' . $namaSiswa;

        // Ambil role Wali Murid
        $role = Role::where('nama_role', 'Wali Murid')->first();
        
        if (!$role) {
            throw new BusinessValidationException(
                'Role Wali Murid tidak ditemukan dalam database. Silakan hubungi administrator.'
            );
        }

        // Buat user baru via repository
        $userData = [
            'role_id' => $role->id,
            'nama' => $nama,
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'is_active' => true,
        ];

        $createdUser = $this->userRepository->create($userData);

        // Return kredensial untuk ditampilkan ke operator
        return [
            'user_id' => $createdUser->id,
            'username' => $username,
            'password' => $password, // Plain text, untuk ditampilkan sekali saja
        ];
    }

    /**
     * Bulk create siswa dari array data.
     * 
     * LOGIKA BISNIS:
     * - Loop setiap row dan create siswa
     * - Optionally create wali murid account untuk setiap siswa
     * - Track success dan error count
     * - Return hasil dengan credentials wali yang dibuat
     *
     * @param array $rows Array of ['nisn' => string, 'nama' => string, 'nomor_hp_wali_murid' => string|null]
     * @param int $kelasId
     * @param bool $createWaliAll
     * @return array{success_count: int, wali_credentials: array}
     * @throws \Exception
     */
    public function bulkCreateSiswa(array $rows, int $kelasId, bool $createWaliAll = false): array
    {
        DB::beginTransaction();
        
        try {
            $successCount = 0;
            $waliCredentials = [];
            
            foreach ($rows as $row) {
                $waliMuridUserId = null;
                
                // Create wali murid account if requested
                if ($createWaliAll) {
                    $waliCred = $this->createWaliMuridAccount(
                        $row['nisn'],
                        $row['nama']
                    );
                    
                    $waliMuridUserId = $waliCred['user_id'];
                    $waliCredentials[] = $waliCred;
                }
                
                // Create siswa
                $siswaArray = [
                    'kelas_id' => $kelasId,
                    'wali_murid_user_id' => $waliMuridUserId,
                    'nisn' => $row['nisn'],
                    'nama_siswa' => $row['nama'],
                    'nomor_hp_wali_murid' => $row['nomor_hp_wali_murid'] ?? null,
                ];
                
                $this->siswaRepository->create($siswaArray);
                $successCount++;
            }
            
            DB::commit();
            
            return [
                'success_count' => $successCount,
                'wali_credentials' => $waliCredentials,
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
