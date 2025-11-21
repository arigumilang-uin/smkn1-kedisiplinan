<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RiwayatPelanggaran;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\JenisPelanggaran; // [PENTING] Model ini dibutuhkan untuk dropdown filter
use Illuminate\Support\Facades\Auth;

class RiwayatController extends Controller
{
    /**
     * MENAMPILKAN DAFTAR RIWAYAT PELANGGARAN (FILTER OTOMATIS)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $role = $user->role->nama_role;

        // 1. Siapkan Data untuk Dropdown Filter
        $allJurusan = Jurusan::all();
        $allKelas = Kelas::all();
        // [FITUR BARU] Data untuk dropdown jenis pelanggaran
        $allPelanggaran = JenisPelanggaran::orderBy('nama_pelanggaran')->get();
        
        // 2. Query Dasar dengan Eager Loading
        // [OPTIMASI] 'siswa.riwayatPelanggaran.jenisPelanggaran' dimuat agar hitung total poin cepat
        $query = RiwayatPelanggaran::with([
            'siswa.kelas.jurusan', 
            'jenisPelanggaran.kategoriPelanggaran', 
            'guruPencatat',
            'siswa.riwayatPelanggaran.jenisPelanggaran'
        ]);

        // ====================================================
        // LOGIKA HAK AKSES DATA (DATA SCOPING)
        // ====================================================

        if ($role == 'Wali Kelas') {
            $kelasBinaan = $user->kelasDiampu;
            if ($kelasBinaan) {
                $query->whereHas('siswa', function($q) use ($kelasBinaan) {
                    $q->where('kelas_id', $kelasBinaan->id);
                });
            } else {
                $query->where('id', 0); 
            }
        } 
        elseif ($role == 'Kaprodi') {
            $jurusanBinaan = $user->jurusanDiampu;
            if ($jurusanBinaan) {
                $query->whereHas('siswa.kelas', function($q) use ($jurusanBinaan) {
                    $q->where('jurusan_id', $jurusanBinaan->id);
                });
            } else {
                 $query->where('id', 0);
            }
        }
        elseif ($role == 'Orang Tua') {
            $anakIds = $user->anakWali->pluck('id');
            $query->whereIn('siswa_id', $anakIds);
        }
        
        // ====================================================
        // LOGIKA FILTER PENCARIAN
        // ====================================================

        // 1. Filter Tanggal
        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_kejadian', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_kejadian', '<=', $request->end_date);
        }
        
        // 2. Filter Jenis Pelanggaran [FITUR BARU]
        if ($request->filled('jenis_pelanggaran_id')) {
            $query->where('jenis_pelanggaran_id', $request->jenis_pelanggaran_id);
        }

        // 3. Filter Pencatat [FITUR BARU - UNTUK LINK DI TABEL]
        if ($request->filled('pencatat_id')) {
            $query->where('guru_pencatat_user_id', $request->pencatat_id);
        }

        // 4. Filter Kelas (Hanya berlaku jika user BUKAN Wali Kelas)
        if ($role != 'Wali Kelas' && $request->filled('kelas_id')) {
            $query->whereHas('siswa', function($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }
        
        // 5. Filter Jurusan (Hanya berlaku jika user BUKAN Kaprodi/Wali Kelas)
        if (!in_array($role, ['Wali Kelas', 'Kaprodi']) && $request->filled('jurusan_id')) {
            $query->whereHas('siswa.kelas', function($q) use ($request) {
                $q->where('jurusan_id', $request->jurusan_id);
            });
        }

        // 6. Cari Nama Siswa
        if ($request->filled('cari_siswa')) {
            $query->whereHas('siswa', function($q) use ($request) {
                $q->where('nama_siswa', 'like', '%' . $request->cari_siswa . '%');
            });
        }

        $riwayat = $query->latest('tanggal_kejadian')->paginate(20)->withQueryString();

        return view('riwayat.index', compact('riwayat', 'allJurusan', 'allKelas', 'allPelanggaran'));
    }
}