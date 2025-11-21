<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Siswa;
use App\Models\Jurusan; // <-- WAJIB ADA
use App\Models\Kelas;   // <-- WAJIB ADA
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::all();
        $query = User::with('role');

        if ($request->filled('cari')) {
            $query->where(function($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->cari . '%')
                  ->orWhere('username', 'like', '%' . $request->cari . '%')
                  ->orWhere('email', 'like', '%' . $request->cari . '%');
            });
        }

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        $users = $query->orderBy('role_id')
                       ->orderBy('nama')
                       ->paginate(10)
                       ->withQueryString();

        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::all();
        
        // DATA PENDUKUNG FILTER (WAJIB ADA AGAR TIDAK ERROR)
        $jurusan = Jurusan::all();
        $kelas = Kelas::orderBy('nama_kelas')->get();

        // Load siswa beserta kelas & jurusan untuk filtering di frontend
        $siswa = Siswa::with('kelas.jurusan')->orderBy('nama_siswa')->get();

        return view('users.create', compact('roles', 'siswa', 'jurusan', 'kelas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'role_id' => 'required|exists:roles,id',
            'password' => 'required|min:6',
            'siswa_ids' => 'nullable|array',
            'siswa_ids.*' => 'exists:siswa,id',
        ]);

        DB::transaction(function() use ($request) {
            $user = User::create([
                'nama' => $request->nama,
                'username' => $request->username,
                'email' => $request->email,
                'role_id' => $request->role_id,
                'password' => Hash::make($request->password),
            ]);

            $roleOrtu = Role::where('nama_role', 'Orang Tua')->first();
            
            if ($roleOrtu && $request->role_id == $roleOrtu->id) {
                if ($request->filled('siswa_ids')) {
                    Siswa::whereIn('id', $request->siswa_ids)->update([
                        'orang_tua_user_id' => $user->id
                    ]);
                }
            }
        });

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan!');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        
        // DATA PENDUKUNG FILTER
        $jurusan = Jurusan::all();
        $kelas = Kelas::orderBy('nama_kelas')->get();
        
        $siswa = Siswa::with('kelas.jurusan')->orderBy('nama_siswa')->get();
        
        // Ambil anak yg sudah terhubung
        $connectedSiswaIds = $user->anakWali->pluck('id')->toArray();

        return view('users.edit', compact('user', 'roles', 'siswa', 'connectedSiswaIds', 'jurusan', 'kelas'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'username' => 'required|unique:users,username,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role_id' => 'required|exists:roles,id',
            'password' => 'nullable|min:6',
            'siswa_ids' => 'nullable|array',
            'siswa_ids.*' => 'exists:siswa,id',
        ]);

        DB::transaction(function() use ($request, $user) {
            $data = [
                'nama' => $request->nama,
                'username' => $request->username,
                'email' => $request->email,
                'role_id' => $request->role_id,
            ];

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            $roleOrtu = Role::where('nama_role', 'Orang Tua')->first();

            if ($roleOrtu && $request->role_id == $roleOrtu->id) {
                // Reset anak lama
                Siswa::where('orang_tua_user_id', $user->id)->update(['orang_tua_user_id' => null]);

                // Set anak baru
                if ($request->filled('siswa_ids')) {
                    Siswa::whereIn('id', $request->siswa_ids)->update([
                        'orang_tua_user_id' => $user->id
                    ]);
                }
            }
        });

        return redirect()->route('users.index')->with('success', 'Data user berhasil diperbarui!');
    }

    public function destroy(User $user)
    {
        if (auth()->id() == $user->id) {
            return back()->with('error', 'Anda tidak bisa menghapus akun sendiri!');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus!');
    }
}