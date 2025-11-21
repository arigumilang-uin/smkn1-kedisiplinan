<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RiwayatPelanggaran;
use App\Models\TindakLanjut;

class OrtuDashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil User Orang Tua yang login
        $user = Auth::user();

        // 2. Ambil SEMUA Data Anak (HasMany)
        $semuaAnak = $user->anakWali; // Ini sekarang me-return Collection (List)

        // Cek jika belum ada anak yang di-link ke akun ini
        if ($semuaAnak->isEmpty()) {
            return view('dashboards.ortu_no_data');
        }

        // 3. LOGIKA PEMILIHAN ANAK (SWITCH CHILD)
        // Cek apakah user memilih spesifik anak via tombol (misal ?siswa_id=10)
        $selectedSiswaId = $request->query('siswa_id');
        
        if ($selectedSiswaId) {
            // Ambil anak yang dipilih, tapi validasi dulu apakah benar anak dia
            $siswaAktif = $semuaAnak->where('id', $selectedSiswaId)->first();
            
            // Security: Jika id ngawur (bukan anaknya), kembalikan ke anak pertama
            if (!$siswaAktif) {
                $siswaAktif = $semuaAnak->first();
            }
        } else {
            // Default: Tampilkan anak pertama di list
            $siswaAktif = $semuaAnak->first();
        }

        // 4. Ambil Statistik Poin (Hanya untuk Anak yang SEDANG AKTIF dipilih)
        $totalPoin = RiwayatPelanggaran::where('siswa_id', $siswaAktif->id)
            ->join('jenis_pelanggaran', 'riwayat_pelanggaran.jenis_pelanggaran_id', '=', 'jenis_pelanggaran.id')
            ->sum('jenis_pelanggaran.poin');

        // 5. Ambil Riwayat Pelanggaran (Anak Aktif)
        $riwayat = RiwayatPelanggaran::with('jenisPelanggaran')
            ->where('siswa_id', $siswaAktif->id)
            ->orderByDesc('tanggal_kejadian')
            ->get();

        // 6. Ambil Kasus / Sanksi (Anak Aktif)
        $kasus = TindakLanjut::where('siswa_id', $siswaAktif->id)
            ->orderByDesc('created_at')
            ->get();

        return view('dashboards.ortu', [
            'semuaAnak' => $semuaAnak, // Kirim list semua anak (untuk bikin tombol switch)
            'siswa' => $siswaAktif,    // Data anak yang sedang ditampilkan
            'totalPoin' => $totalPoin,
            'riwayat' => $riwayat,
            'kasus' => $kasus
        ]);
    }
}