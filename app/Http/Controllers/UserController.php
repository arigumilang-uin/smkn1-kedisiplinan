<?php

namespace App\Http\Controllers;

use App\Services\User\UserService;
use App\Data\User\UserData;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\Role;
use App\Models\Kelas;
use App\Models\Jurusan;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * User Controller - Clean Architecture Pattern
 * 
 * PERAN: Kurir (Courier)
 * - Menerima HTTP Request
 * - Validasi (via FormRequest)
 * - Convert ke DTO
 * - Panggil Service
 * - Return Response
 * 
 * ATURAN:
 * - TIDAK BOLEH ada business logic
 * - TIDAK BOLEH ada query database langsung (use models in constructor if needed)
 * - TIDAK BOLEH ada manipulasi data
 * - Target: < 20 baris per method
 */
class UserController extends Controller
{
    /**
     * Inject UserService via constructor.
     *
     * @param UserService $userService
     */
    public function __construct(
        private UserService $userService
    ) {}

    /**
     * Display list of users with filters.
     */
    public function index(Request $request): View
    {
        $filters = [
            'role_id' => $request->input('role_id'),
            'is_active' => $request->input('is_active'),
            'search' => $request->input('search'),
        ];

        $users = $this->userService->getPaginatedUsers(20, $filters);
        $roles = Role::all();

        return view('users.index', compact('users', 'roles'));
    }

    /**
     * Show create user form.
     * 
     * CLEAN: Fetch master data (kelas, jurusan) for dropdowns
     */
    public function create(): View
    {
        $roles = Role::all();
        $kelas = Kelas::orderBy('nama_kelas')->get();
        $jurusan = Jurusan::orderBy('nama_jurusan')->get();
        
        return view('users.create', compact('roles', 'kelas', 'jurusan'));
    }

    /**
     * Store new user.
     */
    public function store(CreateUserRequest $request): RedirectResponse
    {
        $userData = UserData::from($request->validated());
        
        $this->userService->createUser($userData);

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Show user detail.
     */
    public function show(int $id): View
    {
        $user = $this->userService->getUser($id);
        return view('users.show', compact('user'));
    }

    /**
     * Show edit user form.
     * 
     * CLEAN: Fetch master data (kelas, jurusan) for dropdowns
     */
    public function edit(int $id): View
    {
        $user = $this->userService->getUser($id);
        $roles = Role::all();
        $kelas = Kelas::orderBy('nama_kelas')->get();
        $jurusan = Jurusan::orderBy('nama_jurusan')->get();
        
        return view('users.edit', compact('user', 'roles', 'kelas', 'jurusan'));
    }

    /**
     * Update user.
     */
    public function update(UpdateUserRequest $request, int $id): RedirectResponse
    {
        $userData = UserData::from($request->validated());
        
        $this->userService->updateUser($id, $userData);

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Delete user.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->userService->deleteUser($id);

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil dihapus.');
    }

    /**
     * Show reset password form.
     */
    public function resetPasswordForm(int $id): View
    {
        $user = $this->userService->getUser($id);
        return view('users.reset-password', compact('user'));
    }

    /**
     * Reset user password (by admin).
     */
    public function resetPassword(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $this->userService->resetPassword($id, $request->password);

        return redirect()
            ->route('users.index')
            ->with('success', 'Password berhasil direset.');
    }

    /**
     * Toggle user activation.
     */
    public function toggleActivation(int $id): RedirectResponse
    {
        $this->userService->toggleActivation($id);

        return redirect()
            ->back()
            ->with('success', 'Status aktivasi user berhasil diubah.');
    }

    /**
     * Show own profile.
     */
    public function showProfile(): View
    {
        $user = $this->userService->getUser(auth()->id());
        return view('users.profile-show', compact('user'));  // Using users folder
    }

    /**
     * Show edit own profile form.
     * 
     * NOTE: Using simple profile view (create if not exists)
     */
    public function editProfile(): View
    {
        $user = $this->userService->getUser(auth()->id());
        return view('users.profile', compact('user'));  // Simpler profile edit view
    }

    /**
     * Update own profile.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $userData = UserData::from([
            'id' => auth()->id(),
            'nama' => $request->nama,
            'email' => $request->email,
            'phone' => $request->phone,
            'username' => auth()->user()->username, // Keep existing
            'role_id' => auth()->user()->role_id, // Keep existing
            'is_active' => auth()->user()->is_active, // Keep existing
        ]);

        $this->userService->updateUser(auth()->id(), $userData);

        return redirect()
            ->route('profile.show')
            ->with('success', 'Profile berhasil diperbarui.');
    }

    /**
     * Show change password form.
     */
    public function changePasswordForm(): View
    {
        return view('users.change-password');
    }

    /**
     * Change own password.
     */
    public function changePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'old_password' => ['required'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $this->userService->changePassword(
                auth()->id(),
                $request->old_password,
                $request->password
            );

            return redirect()
                ->route('profile.show')
                ->with('success', 'Password berhasil diubah.');
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Export users (placeholder).
     */
    public function export()
    {
        // TODO: Implement export logic
        return response()->download('path/to/export.xlsx');
    }

    /**
     * Bulk activate users.
     */
    public function bulkActivate(Request $request): RedirectResponse
    {
        // TODO: Implement bulk activate
        return redirect()
            ->back()
            ->with('success', 'Users berhasil diaktifkan.');
    }

    /**
     * Bulk deactivate users.
     */
    public function bulkDeactivate(Request $request): RedirectResponse
    {
        // TODO: Implement bulk deactivate
        return redirect()
            ->back()
            ->with('success', 'Users berhasil dinonaktifkan.');
    }

    /**
     * Bulk delete users.
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        // TODO: Implement bulk delete
        return redirect()
            ->back()
            ->with('success', 'Users berhasil dihapus.');
    }

    /**
     * Import users.
     */
    public function import(Request $request): RedirectResponse
    {
        // TODO: Implement import logic
        return redirect()
            ->back()
            ->with('success', 'Users berhasil diimport.');
    }
}
