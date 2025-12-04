<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RiwayatPelanggaran;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\JenisPelanggaran;
use Illuminate\Support\Facades\Auth;

/**
 * RiwayatController
 *
 * Controller untuk menampilkan riwayat pelanggaran siswa dengan fitur filtering dinamis.
 * Fitur: data scoping berbasis role, filter by tanggal/jenis/pencatat, cari nama siswa, pagination.
 */
class RiwayatController extends Controller
{
    /**
     * Tampilkan daftar riwayat pelanggaran dengan fitur filter (tanggal, jenis, pencatat, siswa).
     * Data di-scope otomatis berdasarkan role user (Wali Kelas, Kaprodi, Wali Murid, Admin/Kepsek).
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Siapkan dropdown filter data
        $allJurusan = Jurusan::all();
        $allKelas = Kelas::all();
        $allPelanggaran = JenisPelanggaran::orderBy('nama_pelanggaran')->get();

        // Mulai query dengan eager loading untuk performa
        $query = RiwayatPelanggaran::with([
            'siswa.kelas.jurusan', 
            'jenisPelanggaran.kategoriPelanggaran', 
            'guruPencatat',
            'siswa.riwayatPelanggaran.jenisPelanggaran'
        ]);

        // Terapkan data scoping berdasarkan role user
        $this->applyRoleBasedScoping($query, $user);

        // Terapkan filter pencarian dari request
        $this->applyFilters($query, $request, $user);

        // Eksekusi query dengan pagination
        $riwayat = $query->latest('tanggal_kejadian')->paginate(20)->withQueryString();

        return view('riwayat.index', compact('riwayat', 'allJurusan', 'allKelas', 'allPelanggaran'));
    }

    /**
     * Terapkan data scoping berdasarkan role user.
     * Wali Kelas: scope ke kelas binaan
     * Kaprodi: scope ke jurusan binaan
     * Wali Murid: scope ke anak-anak sendiri
     * Admin/Kepsek: lihat semua
     */
    private function applyRoleBasedScoping($query, $user): void
    {
        if ($user->hasRole('Wali Kelas')) {
            $kelasBinaan = $user->kelasDiampu;
            if ($kelasBinaan) {
                $query->whereHas('siswa', function($q) use ($kelasBinaan) {
                    $q->where('kelas_id', $kelasBinaan->id);
                });
            } else {
                // Jika Wali Kelas tidak punya kelas binaan, tampilkan 0 records
                $query->where('id', 0);
            }
        } elseif ($user->hasRole('Kaprodi')) {
            $jurusanBinaan = $user->jurusanDiampu;
            if ($jurusanBinaan) {
                $query->whereHas('siswa.kelas', function($q) use ($jurusanBinaan) {
                    $q->where('jurusan_id', $jurusanBinaan->id);
                });
            } else {
                // Jika Kaprodi tidak punya jurusan binaan, tampilkan 0 records
                $query->where('id', 0);
            }
        } elseif ($user->hasRole('Wali Murid')) {
            $anakIds = $user->anakWali->pluck('id');
            $query->whereIn('siswa_id', $anakIds);
        }
        // Jika admin/kepsek, tidak ada scoping (lihat semua)
    }

    /**
     * Terapkan filter pencarian dari request parameters.
     * Filter: tanggal_mulai, tanggal_akhir, jenis_pelanggaran_id, pencatat_id, kelas_id, jurusan_id, cari_siswa
     */
    private function applyFilters($query, Request $request, $user): void
    {
        // Filter by tanggal range
        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_kejadian', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_kejadian', '<=', $request->end_date);
        }

        // Filter by jenis pelanggaran
        if ($request->filled('jenis_pelanggaran_id')) {
            $query->where('jenis_pelanggaran_id', $request->jenis_pelanggaran_id);
        }

        // Filter by guru pencatat (untuk link di tabel)
        if ($request->filled('pencatat_id')) {
            $query->where('guru_pencatat_user_id', $request->pencatat_id);
        }

        // Filter by kelas (hanya jika user bukan Wali Kelas)
        if (!$user->hasRole('Wali Kelas') && $request->filled('kelas_id')) {
            $query->whereHas('siswa', function($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }

        // Filter by jurusan (hanya jika user bukan Kaprodi atau Wali Kelas)
        if (!$user->hasAnyRole(['Wali Kelas', 'Kaprodi']) && $request->filled('jurusan_id')) {
            $query->whereHas('siswa.kelas', function($q) use ($request) {
                $q->where('jurusan_id', $request->jurusan_id);
            });
        }

        // Cari berdasarkan nama siswa
        if ($request->filled('cari_siswa')) {
            $query->whereHas('siswa', function($q) use ($request) {
                $q->where('nama_siswa', 'like', '%' . $request->cari_siswa . '%');
            });
        }
    }
}