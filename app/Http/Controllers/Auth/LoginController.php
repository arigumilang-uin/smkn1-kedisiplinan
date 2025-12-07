<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Services\User\RoleService;

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
     * 
     * User bisa login dengan:
     * - Username + Password, atau
     * - Email + Password
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

        $loginField = $request->username;
        $password = $request->password;

        // --- Tentukan apakah input adalah email atau username ---
        // Cek apakah input mengandung karakter '@' (indikator email)
        $isEmail = filter_var($loginField, FILTER_VALIDATE_EMAIL) !== false;

        // --- Coba Login ---
        // Jika input adalah email, coba login dengan email
        // Jika bukan email, coba login dengan username
        $attempted = false;
        
        if ($isEmail) {
            // Coba login dengan email
            $attempted = Auth::attempt(['email' => $loginField, 'password' => $password], $remember);
        }
        
        // Jika login dengan email gagal, atau input bukan email, coba dengan username
        if (!$attempted) {
            $attempted = Auth::attempt(['username' => $loginField, 'password' => $password], $remember);
        }

        if ($attempted) {
            // --- BERHASIL LOGIN ---
            
            // 1. Regenerasi session untuk keamanan
            $request->session()->regenerate();

            // 2. Ambil data user yang login
            $user = Auth::user();
            
            // 3. CEK APAKAH AKUN AKTIF
            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'username' => 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.',
                ])->onlyInput('username');
            }
            
            // 4. Update last login timestamp
            $user->update(['last_login_at' => now()]);

            // 5. LOGIKA PENGALIHAN (REDIRECT) BERDASARKAN PERAN
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
            } elseif ($user->hasRole('Waka Sarana')) {
                return redirect()->intended('/dashboard/waka-sarana');
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
            'username' => 'Username/Email atau password salah.',
        ])->onlyInput('username'); // Kembalikan ke form dengan data username/email
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
