<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use App\Models\RiwayatPelanggaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataJurusanController extends Controller
{
    /**
     * Display list of jurusan with statistics (read-only)
     */
    public function index()
    {
        $jurusanList = Jurusan::withCount('kelas')
            ->with('kelas.siswa')
            ->get()
            ->map(function ($jurusan) {
                $siswaIds = $jurusan->kelas->flatMap(fn($k) => $k->siswa->pluck('id'));
                
                return [
                    'jurusan' => $jurusan,
                    'total_siswa' => $siswaIds->count(),
                    'total_kelas' => $jurusan->kelas->count(),
                    'total_pelanggaran' => RiwayatPelanggaran::whereIn('siswa_id', $siswaIds)->count(),
                    'pelanggaran_bulan_ini' => RiwayatPelanggaran::whereIn('siswa_id', $siswaIds)
                        ->whereMonth('tanggal_kejadian', now()->month)
                        ->count(),
                ];
            });

        return view('data_jurusan.index', compact('jurusanList'));
    }

    /**
     * Display detail jurusan with charts and statistics
     */
    public function show(Jurusan $jurusan)
    {
        $jurusan->load(['kelas.siswa', 'kaprodi']);
        
        $siswaIds = $jurusan->kelas->flatMap(fn($k) => $k->siswa->pluck('id'));
        
        // Statistics
        $stats = [
            'total_siswa' => $siswaIds->count(),
            'total_kelas' => $jurusan->kelas->count(),
            'total_pelanggaran' => RiwayatPelanggaran::whereIn('siswa_id', $siswaIds)->count(),
            'pelanggaran_bulan_ini' => RiwayatPelanggaran::whereIn('siswa_id', $siswaIds)
                ->whereMonth('tanggal_kejadian', now()->month)
                ->count(),
        ];
        
        // Chart: Pelanggaran per bulan (last 6 months)
        $chartData = RiwayatPelanggaran::whereIn('siswa_id', $siswaIds)
            ->where('tanggal_kejadian', '>=', now()->subMonths(6))
            ->selectRaw('MONTH(tanggal_kejadian) as bulan, YEAR(tanggal_kejadian) as tahun, COUNT(*) as total')
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->get();
        
        // Top 10 siswa dengan pelanggaran terbanyak
        $topSiswa = DB::table('riwayat_pelanggaran')
            ->select('siswa_id', DB::raw('COUNT(*) as total_pelanggaran'))
            ->whereIn('siswa_id', $siswaIds)
            ->groupBy('siswa_id')
            ->orderByDesc('total_pelanggaran')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $siswa = \App\Models\Siswa::with('kelas')->find($item->siswa_id);
                return [
                    'siswa' => $siswa,
                    'total_pelanggaran' => $item->total_pelanggaran,
                ];
            });
        
        // Pelanggaran per kategori
        $pelanggaranPerKategori = RiwayatPelanggaran::whereIn('siswa_id', $siswaIds)
            ->join('jenis_pelanggaran', 'riwayat_pelanggaran.jenis_pelanggaran_id', '=', 'jenis_pelanggaran.id')
            ->join('kategori_pelanggaran', 'jenis_pelanggaran.kategori_id', '=', 'kategori_pelanggaran.id')
            ->select('kategori_pelanggaran.nama_kategori', DB::raw('COUNT(*) as total'))
            ->groupBy('kategori_pelanggaran.id', 'kategori_pelanggaran.nama_kategori')
            ->get();
        
        return view('data_jurusan.show', compact('jurusan', 'stats', 'chartData', 'topSiswa', 'pelanggaranPerKategori'));
    }
}
