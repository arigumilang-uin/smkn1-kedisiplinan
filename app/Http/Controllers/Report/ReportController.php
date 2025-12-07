<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\RiwayatPelanggaran;
use App\Models\Siswa;
use App\Models\Jurusan;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Show report index/builder
     */
    public function index()
    {
        $jurusans = Jurusan::all();
        $kelas = Kelas::all();
        
        return view('kepala_sekolah.reports.index', [
            'jurusans' => $jurusans,
            'kelas' => $kelas,
        ]);
    }

    /**
     * Generate report preview (HTML)
     */
    public function preview(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:pelanggaran,siswa,tindakan',
            'jurusan_id' => 'nullable|exists:jurusan,id',
            'kelas_id' => 'nullable|exists:kelas,id',
            'periode_mulai' => 'nullable|date',
            'periode_akhir' => 'nullable|date',
        ]);

        $query = RiwayatPelanggaran::query();

        // Apply filters
        if ($request->filled('jurusan_id')) {
            $query->whereHas('siswa.kelas', function($q) use ($request) {
                $q->where('jurusan_id', $request->jurusan_id);
            });
        }

        if ($request->filled('kelas_id')) {
            $query->whereHas('siswa', function($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }

        if ($request->filled('periode_mulai')) {
            $query->where('tanggal_kejadian', '>=', $request->periode_mulai);
        }

        if ($request->filled('periode_akhir')) {
            $query->where('tanggal_kejadian', '<=', $request->periode_akhir);
        }

        $data = $query->with(['siswa.kelas.jurusan', 'jenisPelanggaran', 'user'])
                      ->orderBy('tanggal_kejadian', 'desc')
                      ->get();

        // Store in session for export
        session(['report_data' => $data, 'report_filters' => $request->all()]);

        return view('kepala_sekolah.reports.preview', [
            'data' => $data,
            'filters' => $request->all(),
            'reportType' => $request->report_type,
        ]);
    }

    /**
     * Export to CSV
     */
    public function exportCsv()
    {
        $data = session('report_data', []);
        $filters = session('report_filters', []);

        if (empty($data)) {
            return redirect()->route('kepala-sekolah.reports.index')
                ->with('error', 'Tidak ada data untuk diexport. Buat laporan terlebih dahulu.');
        }

        $filename = 'laporan_pelanggaran_' . now()->format('Ymd_His') . '.csv';

        $callback = function() use ($data) {
            // UTF-16LE BOM untuk Excel
            echo "\xFF\xFE";
            
            $headerRow = "NISN\tNama Siswa\tKelas\tJurusan\tJenis Pelanggaran\tTanggal\tDikategorikan Oleh\n";
            echo mb_convert_encoding($headerRow, 'UTF-16LE', 'UTF-8');
            
            foreach ($data as $row) {
                $dataRow = (
                    ($row->siswa->nisn ?? '') . "\t" .
                    ($row->siswa->nama_siswa ?? '') . "\t" .
                    ($row->siswa->kelas->nama_kelas ?? '') . "\t" .
                    ($row->siswa->kelas->jurusan->nama_jurusan ?? '') . "\t" .
                    ($row->jenisPelanggaran->nama ?? '') . "\t" .
                    ($row->tanggal_kejadian ?? '') . "\t" .
                    ($row->user->nama ?? '') . "\n"
                );
                echo mb_convert_encoding($dataRow, 'UTF-16LE', 'UTF-8');
            }
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-16LE',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Export to PDF (requires mailable/dompdf)
     * Note: Install via: composer require barryvdh/laravel-dompdf
     */
    public function exportPdf()
    {
        $data = session('report_data', []);
        $filters = session('report_filters', []);

        if (empty($data)) {
            return redirect()->route('kepala-sekolah.reports.index')
                ->with('error', 'Tidak ada data untuk diexport. Buat laporan terlebih dahulu.');
        }

        // Check if dompdf is installed
        if (!class_exists('Barryvdh\DomPDF\Facade\Pdf')) {
            return redirect()->back()
                ->with('warning', 'PDF export belum dikonfigurasi. Gunakan CSV untuk sekarang.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('kepala_sekolah.reports.pdf', [
            'data' => $data,
            'filters' => $filters,
        ]);

        return $pdf->download('laporan_pelanggaran_' . now()->format('Ymd_His') . '.pdf');
    }
}


