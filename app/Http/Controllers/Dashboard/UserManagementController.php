<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserManagementController extends Controller
{
    /**
     * List all users
     */
    public function index(Request $request)
    {
        $query = User::with('role');

        // Filter by role
        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('username', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->orderBy('nama')->paginate(15);
        $roles = Role::all();

        return view('kepala_sekolah.users.index', [
            'users' => $users,
            'roles' => $roles,
            'selectedRole' => $request->role_id,
        ]);
    }

    /**
     * Show user detail
     */
    public function show(User $user)
    {
        $user->load('role');
        
        return view('kepala_sekolah.users.show', [
            'user' => $user,
        ]);
    }

    /**
     * Reset password untuk user
     */
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return redirect()->route('kepala-sekolah.users.show', $user->id)
            ->with('success', 'Password berhasil direset.');
    }

    /**
     * Disable/Enable user account
     */
    public function toggleStatus(User $user)
    {
        $currentStatus = $user->is_active ?? true;
        $newStatus = !$currentStatus;

        $user->update(['is_active' => $newStatus]);

        $message = $newStatus 
            ? "Akun {$user->nama} berhasil diaktifkan." 
            : "Akun {$user->nama} berhasil dinonaktifkan.";

        return redirect()->back()->with('success', $message);
    }
}
