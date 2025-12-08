<?php

namespace App\Services\Siswa;

use App\Data\Siswa\SiswaData;
use App\Data\Siswa\SiswaFilterData;
use App\Data\User\UserData;
use App\Repositories\Contracts\SiswaRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\User\UserNamingService;
use App\Models\Role;
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
        private UserRepositoryInterface $userRepository
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
     * @throws \Exception
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

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
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
            throw new \Exception('Role Wali Murid tidak ditemukan dalam database.');
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
}
