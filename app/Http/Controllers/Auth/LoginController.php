<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Services\RoleService;

class LoginController extends Controller
{
    /**
     * Controller untuk autentikasi (login/logout).
     * Komentar dan pengalihan disesuaikan berdasarkan role efektif pengguna.
     */
    /**
     * 1. Menampilkan halaman formulir login.
     * (Menangani: GET / )
     */
    public function showLoginForm(): View
    {
        // 'auth.login' adalah file view yang akan kita buat
        // di resources/views/auth/login.blade.php
        return view('auth.login');
    }

    /**
     * 2. Memproses upaya login.
     * (Menangani: POST / )
     */
    public function login(Request $request): RedirectResponse
    {
        // --- Validasi Input ---
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Cek apakah user mencentang "Ingat Saya"
        $remember = $request->has('remember');

        // --- Coba Login ---
        // Kita login menggunakan 'username', BUKAN 'email'
        if (Auth::attempt(['username' => $request->username, 'password' => $request->password], $remember)) {
            
            // --- BERHASIL LOGIN ---
            
            // 1. Regenerasi session untuk keamanan
            $request->session()->regenerate();

            // 2. Ambil data user yang login
            $user = Auth::user();

            // 3. LOGIKA PENGALIHAN (REDIRECT) BERDASARKAN PERAN
            // Gunakan helper hasRole/hasAnyRole untuk keputusan
            if (!$user->role) {
                Auth::logout();
                return redirect('/')->withErrors(['username' => 'Role tidak valid.']);
            }

            // Jika user adalah role Developer yang sesungguhnya, arahkan ke dashboard Developer khusus
            if (RoleService::isRealDeveloper($user)) {
                return redirect()->intended('/dashboard/developer');
            }

            if ($user->hasAnyRole(['Waka Kesiswaan', 'Operator Sekolah'])) {
                return redirect()->intended('/dashboard/admin');
            } elseif ($user->hasRole('Kepala Sekolah')) {
                return redirect()->intended('/dashboard/kepsek');
            } elseif ($user->hasRole('Kaprodi')) {
                return redirect()->intended('/dashboard/kaprodi');
            } elseif ($user->hasRole('Wali Kelas')) {
                return redirect()->intended('/dashboard/walikelas');
            } elseif ($user->hasRole('Guru')) {
                return redirect()->intended('/pelanggaran/catat');
            } elseif ($user->hasRole('Wali Murid')) {
                return redirect()->intended('/dashboard/wali_murid');
            } else {
                Auth::logout();
                return redirect('/')->withErrors(['username' => 'Role tidak valid.']);
            }

        }

        // --- GAGAL LOGIN ---
        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username'); // Kembalikan ke form dengan data username
    }

    /**
     * 3. Memproses logout.
     * (Menangani: POST /logout )
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Kembali ke halaman login
        return redirect('/');
    }
}