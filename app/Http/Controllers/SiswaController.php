<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\User;
use App\Models\Jurusan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiswaController extends Controller
{
    /**
     * MENAMPILKAN DAFTAR SISWA (DENGAN FILTER OTOMATIS PER ROLE)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $role = $user->role->nama_role;

        // 1. Siapkan Data untuk Dropdown Filter
        $allJurusan = Jurusan::all();
        $allKelas = Kelas::orderBy('nama_kelas')->get();

        // 2. Query Dasar
        $query = Siswa::with('kelas.jurusan');

        // ====================================================
        // LOGIKA HAK AKSES DATA (DATA SCOPING)
        // ====================================================
        
        // SKENARIO A: WALI KELAS (Hanya lihat kelas binaannya)
        if ($role == 'Wali Kelas') {
            $kelasBinaan = $user->kelasDiampu;
            
            if ($kelasBinaan) {
                // Paksa filter ke ID kelas binaan
                $query->where('kelas_id', $kelasBinaan->id);
            } else {
                // Jika user ini Wali Kelas tapi belum di-assign kelas oleh Admin
                // Tampilkan kosong agar tidak error, atau bisa redirect dengan pesan error
                $query->where('id', 0); 
            }
        }
        // SKENARIO B: KAPRODI (Hanya lihat jurusan binaannya)
        elseif ($role == 'Kaprodi') {
            $jurusanBinaan = $user->jurusanDiampu;
            
            if ($jurusanBinaan) {
                // Paksa filter siswa yang kelasnya ada di jurusan ini
                $query->whereHas('kelas', function($q) use ($jurusanBinaan) {
                    $q->where('jurusan_id', $jurusanBinaan->id);
                });
            } else {
                $query->where('id', 0);
            }
        }

        // ====================================================
        // LOGIKA FILTER PENCARIAN (DARI FORM)
        // ====================================================

        // 1. Pencarian Keyword
        if ($request->filled('cari')) {
            $query->where(function($q) use ($request) {
                $q->where('nama_siswa', 'like', '%' . $request->cari . '%')
                  ->orWhere('nisn', 'like', '%' . $request->cari . '%');
            });
        }

        // 2. Filter Kelas (Hanya berlaku jika user BUKAN Wali Kelas)
        // Karena Wali Kelas sudah difilter paksa di atas, filter manual ini diabaikan untuk mereka
        if ($role != 'Wali Kelas' && $request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        // 3. Filter Jurusan (Hanya berlaku jika user BUKAN Kaprodi/Wali Kelas)
        if (!in_array($role, ['Wali Kelas', 'Kaprodi']) && $request->filled('jurusan_id')) {
             $query->whereHas('kelas', function($q) use ($request) {
                $q->where('jurusan_id', $request->jurusan_id);
            });
        }

        // 4. Filter Tingkat (Angkatan)
        if ($request->filled('tingkat')) {
            $query->whereHas('kelas', function($q) use ($request) {
                $q->where('nama_kelas', 'like', $request->tingkat . ' %');
            });
        }

        // 3. Eksekusi & Pagination
        $siswa = $query->orderBy('kelas_id')->orderBy('nama_siswa')
                       ->paginate(20)->withQueryString();

        return view('siswa.index', compact('siswa', 'allJurusan', 'allKelas'));
    }

    /**
     * TAMPILKAN FORM TAMBAH SISWA
     */
    public function create()
    {
        $kelas = Kelas::all();
        // Ambil user yang rolenya 'Orang Tua' untuk dropdown
        $orangTua = User::whereHas('role', function($q){
            $q->where('nama_role', 'Orang Tua');
        })->get();

        return view('siswa.create', compact('kelas', 'orangTua'));
    }

    /**
     * SIMPAN DATA SISWA BARU
     */
    public function store(Request $request)
    {
        $request->validate([
            'nisn' => 'required|unique:siswa,nisn',
            'nama_siswa' => 'required',
            'kelas_id' => 'required',
            'nomor_hp_ortu' => 'nullable|numeric',
            // 'orang_tua_user_id' opsional, bisa diisi belakangan via menu User
        ]);

        Siswa::create($request->all());

        return redirect()->route('siswa.index')->with('success', 'Data Siswa Berhasil Ditambahkan');
    }

    /**
     * TAMPILKAN FORM EDIT
     */
    public function edit(Siswa $siswa)
    {
        $kelas = Kelas::all();
        $orangTua = User::whereHas('role', function($q){
            $q->where('nama_role', 'Orang Tua');
        })->get();

        return view('siswa.edit', compact('siswa', 'kelas', 'orangTua'));
    }

    /**
     * UPDATE DATA SISWA
     */
    public function update(Request $request, Siswa $siswa)
    {
        $request->validate([
            'nisn' => 'required|unique:siswa,nisn,' . $siswa->id, // Abaikan ID ini saat cek unique
            'nama_siswa' => 'required',
            'kelas_id' => 'required',
        ]);

        $siswa->update($request->all());

        return redirect()->route('siswa.index')->with('success', 'Data Siswa Berhasil Diupdate');
    }

    /**
     * HAPUS SISWA
     */
    public function destroy(Siswa $siswa)
    {
        $siswa->delete();
        return redirect()->route('siswa.index')->with('success', 'Data Siswa Berhasil Dihapus');
    }
}