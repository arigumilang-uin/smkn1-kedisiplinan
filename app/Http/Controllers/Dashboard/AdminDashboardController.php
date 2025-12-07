<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Traits\HasStatistics;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
// Import Model
use App\Models\RiwayatPelanggaran;
use App\Models\TindakLanjut;
use App\Models\Siswa;
use App\Models\User;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\JenisPelanggaran;

class AdminDashboardController extends Controller
{
    use HasStatistics;

    public function index(Request $request): View
        {
            $user = Auth::user();

            // =============================================================
            // SCENARIO A: OPERATOR SEKOLAH
            // =============================================================
            if ($user->hasRole('Operator Sekolah')) {
                $totalUser = User::count();
                $totalSiswa = Siswa::count();
                $totalKelas = Kelas::count();
                $totalJurusan = Jurusan::count();
                $totalAturan = JenisPelanggaran::count();

                return view('dashboards.operator', compact(
                    'totalUser', 'totalSiswa', 'totalKelas', 'totalJurusan', 'totalAturan'
                ));
            }

            // =============================================================
            // SCENARIO B: WAKA KESISWAAN
            // =============================================================
            
            // 1. SIAPKAN DATA FILTER
            $allJurusan = Jurusan::all();
            $allKelas = Kelas::all(); // Nanti kita urutkan di view atau query
            
            // Tangkap Input Filter
            $startDate = $request->input('start_date', date('Y-m-01'));
            $endDate = $request->input('end_date', date('Y-m-d'));
            $jurusanId = $request->input('jurusan_id');
            $kelasId = $request->input('kelas_id');
            $angkatan = $request->input('angkatan'); // <--- BARU: Filter Tingkat (X, XI, XII)
            $chartType = $request->input('chart_type', 'doughnut');

            // 2. QUERY DASAR RIWAYAT
            $queryRiwayat = RiwayatPelanggaran::query()
                ->whereDate('tanggal_kejadian', '>=', $startDate)
                ->whereDate('tanggal_kejadian', '<=', $endDate);

            // --- LOGIKA FILTER PRIORITAS (HIERARKI) ---
            
            if ($kelasId) {
                // PRIORITAS 1: Jika Kelas spesifik dipilih, abaikan Jurusan & Angkatan
                $queryRiwayat->whereHas('siswa', function($q) use ($kelasId) {
                    $q->where('kelas_id', $kelasId);
                });
            } else {
                // PRIORITAS 2: Jika Kelas KOSONG, baru cek Jurusan & Angkatan
                
                // Filter Jurusan
                if ($jurusanId) {
                    $queryRiwayat->whereHas('siswa.kelas', function($q) use ($jurusanId) {
                        $q->where('jurusan_id', $jurusanId);
                    });
                }

                // Filter Angkatan (Tingkat) - BARU
                if ($angkatan) {
                    $queryRiwayat->whereHas('siswa.kelas', function($q) use ($angkatan) {
                        // Asumsi format nama kelas: "XII ATP 1", "X TKJ 2"
                        // Kita cari yang depannya cocok dengan string angkatan
                        $q->where('nama_kelas', 'like', $angkatan . ' %'); 
                    });
                }
            }

            // 3. HITUNG STATISTIK (Berdasarkan Query Filter di atas)
            $pelanggaranFiltered = (clone $queryRiwayat)->count();
            
            // Grafik
            $statistikPelanggaran = (clone $queryRiwayat)
                ->select('jenis_pelanggaran_id', DB::raw('count(*) as total'))
                ->groupBy('jenis_pelanggaran_id')
                ->with('jenisPelanggaran')
                ->orderByDesc('total')
                ->take(5)->get();

            $chartLabels = $statistikPelanggaran->pluck('jenisPelanggaran.nama_pelanggaran');
            $chartData = $statistikPelanggaran->pluck('total');

            // 4. TABEL KASUS TERBARU (Terapkan Logika Filter yang SAMA)
            $daftarKasus = TindakLanjut::with(['siswa.kelas', 'suratPanggilan'])
                ->whereHas('siswa.kelas', function($q) use ($jurusanId, $kelasId, $angkatan) {
                    // Terapkan logika prioritas yang sama persis
                    if ($kelasId) {
                        $q->where('id', $kelasId);
                    } else {
                        if ($jurusanId) $q->where('jurusan_id', $jurusanId);
                        if ($angkatan) $q->where('nama_kelas', 'like', $angkatan . ' %');
                    }
                })
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->latest()->take(10)->get();

            // Data Kartu Statis (Global, atau mau difilter juga? Biasanya global)
            $totalSiswa = Siswa::count();
            $kasusAktif = TindakLanjut::whereIn('status', ['Baru', 'Menunggu Persetujuan'])->count();
            $butuhPersetujuan = TindakLanjut::where('status', 'Menunggu Persetujuan')->count();

            return view('dashboards.waka', compact(
                'totalSiswa', 'pelanggaranFiltered', 'kasusAktif', 'butuhPersetujuan',
                'daftarKasus', 'chartLabels', 'chartData', 'chartType',
                'allJurusan', 'allKelas', 'startDate', 'endDate'
            ));
        }
    }