<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  (Daftar role yang dibolehkan, dipisah koma)
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Cek apakah user login
        if (!Auth::check()) {
            return redirect('login');
        }

        // 2. Ambil role user saat ini
        $userRole = Auth::user()->role->nama_role;

        // 3. Cek apakah role user ada di dalam daftar yang diizinkan
        // Contoh penggunaan di route: middleware('role:Kepala Sekolah,Waka Kesiswaan')
        if (in_array($userRole, $roles)) {
            return $next($request); // Silakan lewat
        }

        // 4. Jika tidak cocok, tendang balik (Abort 403 Forbidden)
        abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk mengakses halaman ini.');
    }
}   