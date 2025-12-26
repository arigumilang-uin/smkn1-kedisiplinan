<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\RiwayatPelanggaran;
use App\Models\TindakLanjut;
use App\Models\Kelas;
use App\Models\Jurusan;

class KaprodiDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get jurusan yang diampu
        $jurusan = $user->jurusanDiampu;
        
        // Jika tidak punya jurusan, tampilkan no data
        if (!$jurusan) {
            return view('dashboards.kaprodi_no_data');
        }
        
        // Get Program Keahlian jika ada (untuk display)
        $programKeahlian = $jurusan->programKeahlian;

        // FILTER (Default: Bulan Ini)
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-d'));
        $kelasId = $request->input('kelas_id'); // Filter per kelas (optional)
        $jurusanId = $request->input('jurusan_id'); // Filter per jurusan (NEW)

        // Get all jurusan IDs yang dikelola kaprodi
        $jurusanIds = $user->getJurusanIdsForKaprodi();
        
        // If specific jurusan filter, validate it's in their scope
        if ($jurusanId && in_array($jurusanId, $jurusanIds)) {
            $jurusanIds = [$jurusanId];
        }

        // DATA JURUSAN UNTUK DROPDOWN (NEW)
        $jurusanList = Jurusan::whereIn('id', $user->getJurusanIdsForKaprodi())->get();

        // DATA KELAS UNTUK DROPDOWN (filter by jurusan scope)
        $kelasJurusan = Kelas::whereIn('jurusan_id', $jurusanIds)->get();

        // SISWA IDS (untuk scope filtering)
        $siswaIds = DB::table('siswa')
            ->join('kelas', 'siswa.kelas_id', '=', 'kelas.id')
            ->whereIn('kelas.jurusan_id', $jurusanIds)
            ->when($kelasId, function($q) use ($kelasId) {
                return $q->where('kelas.id', $kelasId);
            })
            ->pluck('siswa.id');

        // KASUS SURAT (Clean & Informatif)
        $kasusBaru = TindakLanjut::with(['siswa.kelas', 'suratPanggilan'])
            ->whereIn('siswa_id', $siswaIds)
            ->forPembina('Kaprodi')
            ->whereHas('suratPanggilan')
            ->whereIn('status', ['Baru', 'Menunggu Persetujuan', 'Disetujui', 'Ditangani'])
            ->latest()
            ->get();

        // DIAGRAM: Pelanggaran Populer
        $chartPelanggaran = DB::table('riwayat_pelanggaran')
            ->join('siswa', 'riwayat_pelanggaran.siswa_id', '=', 'siswa.id')
            ->join('kelas', 'siswa.kelas_id', '=', 'kelas.id')
            ->join('jenis_pelanggaran', 'riwayat_pelanggaran.jenis_pelanggaran_id', '=', 'jenis_pelanggaran.id')
            ->whereIn('kelas.jurusan_id', $jurusanIds)
            ->when($kelasId, function($q) use ($kelasId) {
                return $q->where('kelas.id', $kelasId);
            })
            ->whereDate('riwayat_pelanggaran.tanggal_kejadian', '>=', $startDate)
            ->whereDate('riwayat_pelanggaran.tanggal_kejadian', '<=', $endDate)
            ->select('jenis_pelanggaran.nama_pelanggaran', DB::raw('count(*) as total'))
            ->groupBy('jenis_pelanggaran.nama_pelanggaran')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $chartLabels = $chartPelanggaran->pluck('nama_pelanggaran');
        $chartData = $chartPelanggaran->pluck('total');

        // STATISTIK
        $totalSiswa = $siswaIds->count();
        $totalKasus = $kasusBaru->count();
        $totalPelanggaran = RiwayatPelanggaran::whereIn('siswa_id', $siswaIds)
            ->whereDate('tanggal_kejadian', '>=', $startDate)
            ->whereDate('tanggal_kejadian', '<=', $endDate)
            ->count();

        return view('dashboards.kaprodi', compact(
            'programKeahlian',  // NEW
            'jurusan',          // Legacy (for backward compat)
            'jurusanList',      // NEW: dropdown
            'kasusBaru',
            'chartLabels', 
            'chartData',
            'totalSiswa',
            'totalKasus',
            'totalPelanggaran',
            'kelasJurusan', 
            'startDate', 
            'endDate'
        ));
    }
}