<?php

namespace App\Services\User;

use App\Data\User\UserData;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * User Service - The Orchestrator
 * 
 * Tanggung jawab:
 * - User management (CRUD)
 * - Role assignment
 * - Password management
 * - Account activation/deactivation
 * 
 * CRITICAL: Service ini TIDAK BOLEH menerima Request object.
 * Semua input harus berupa DTO atau primitive types.
 */
class UserService
{
    /**
     * UserService constructor.
     *
     * @param UserRepositoryInterface $userRepo
     */
    public function __construct(
        private UserRepositoryInterface $userRepo
    ) {}

    /**
     * Create user baru.
     * 
     * ALUR:
     * 1. Validate role_id exists
     * 2. Hash password
     * 3. Create user via repository
     * 4. Return created user data
     *
     * @param UserData $data
     * @return UserData
     * @throws \Exception
     */
    public function createUser(UserData $data): UserData
    {
        DB::beginTransaction();

        try {
            // Validate role exists
            $role = Role::findOrFail($data->role_id);

            // Siapkan data untuk disimpan
            $userData = [
                'role_id' => $data->role_id,
                'nama' => $data->nama,
                'username' => $data->username,
                'email' => $data->email,
                'phone' => $data->phone,
                'nip' => $data->nip,
                'nuptk' => $data->nuptk,
                'password' => Hash::make($data->password),
                'is_active' => $data->is_active ?? true,
            ];

            // Create via repository
            $createdUser = $this->userRepo->create($userData);

            DB::commit();

            return UserData::from($createdUser);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update user.
     * 
     * ALUR:
     * 1. Validate role_id exists (if changed)
     * 2. Update user data via repository
     * 3. Return updated user data
     *
     * @param int $userId
     * @param UserData $data
     * @return UserData
     * @throws \Exception
     */
    public function updateUser(int $userId, UserData $data): UserData
    {
        DB::beginTransaction();

        try {
            // Validate role exists if provided
            if ($data->role_id) {
                Role::findOrFail($data->role_id);
            }

            // Siapkan data untuk update
            $updateData = [
                'nama' => $data->nama,
                'email' => $data->email,
                'phone' => $data->phone,
                'nip' => $data->nip,
                'nuptk' => $data->nuptk,
                'is_active' => $data->is_active,
            ];

            // Update role jika ada
            if ($data->role_id) {
                $updateData['role_id'] = $data->role_id;
            }

            // Update username jika ada dan berubah
            if ($data->username) {
                $updateData['username'] = $data->username;
                $updateData['username_changed_at'] = now();
            }

            // Update via repository
            $updatedUser = $this->userRepo->update($userId, $updateData);

            DB::commit();

            return UserData::from($updatedUser);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete user.
     *
     * @param int $userId
     * @return bool
     */
    public function deleteUser(int $userId): bool
    {
        return $this->userRepo->delete($userId);
    }

    /**
     * Reset user password.
     *
     * @param int $userId
     * @param string $newPassword
     * @return bool
     */
    public function resetPassword(int $userId, string $newPassword): bool
    {
        return $this->userRepo->updatePassword($userId, $newPassword);
    }

    /**
     * Change user password (dengan verify old password).
     *
     * @param int $userId
     * @param string $oldPassword
     * @param string $newPassword
     * @return bool
     * @throws \Exception
     */
    public function changePassword(int $userId, string $oldPassword, string $newPassword): bool
    {
        $user = $this->userRepo->find($userId);

        // Verify old password
        if (!Hash::check($oldPassword, $user->password)) {
            throw new \Exception('Password lama tidak sesuai');
        }

        return $this->userRepo->updatePassword($userId, $newPassword);
    }

    /**
     * Toggle user activation status.
     *
     * @param int $userId
     * @return bool
     */
    public function toggleActivation(int $userId): bool
    {
        return $this->userRepo->toggleActivation($userId);
    }

    /**
     * Get paginated users with filters.
     *
     * @param int $perPage
     * @param array $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPaginatedUsers(int $perPage = 20, array $filters = [])
    {
        return $this->userRepo->getPaginatedUsers($perPage, $filters);
    }

    /**
     * Get user by ID.
     *
     * @param int $userId
     * @return mixed
     */
    public function getUser(int $userId)
    {
        return $this->userRepo->find($userId);
    }

    /**
     * Get users by role name.
     *
     * @param string $roleName
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUsersByRole(string $roleName)
    {
        return $this->userRepo->findByRoleName($roleName);
    }

    /**
     * Get all active users.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveUsers()
    {
        return $this->userRepo->getActiveUsers();
    }

    /**
     * Get all teachers.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTeachers()
    {
        return $this->userRepo->getTeachers();
    }

    /**
     * Check if username exists.
     *
     * @param string $username
     * @param int|null $excludeUserId
     * @return bool
     */
    public function usernameExists(string $username, ?int $excludeUserId = null): bool
    {
        return $this->userRepo->usernameExists($username, $excludeUserId);
    }

    /**
     * Check if email exists.
     *
     * @param string $email
     * @param int|null $excludeUserId
     * @return bool
     */
    public function emailExists(string $email, ?int $excludeUserId = null): bool
    {
        return $this->userRepo->emailExists($email, $excludeUserId);
    }
}
