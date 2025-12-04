<?php

namespace App\Http\Controllers;

use App\Models\JenisPelanggaran;
use App\Models\KategoriPelanggaran;
use Illuminate\Http\Request;

/**
 * JenisPelanggaranController
 *
 * Controller untuk mengelola master data jenis pelanggaran (CRUD).
 * Fitur: index dengan search/pagination, create/edit form, delete dengan proteksi data.
 * Proteksi: tidak bisa hapus jenis pelanggaran yang sudah tercatat di riwayat siswa.
 */
class JenisPelanggaranController extends Controller
{
    /**
     * Tampilkan daftar jenis pelanggaran dengan fitur pencarian.
     */
    public function index(Request $request)
    {
        $query = JenisPelanggaran::with('kategoriPelanggaran');

        // Pencarian berdasarkan nama pelanggaran
        if ($request->filled('cari')) {
            $query->where('nama_pelanggaran', 'like', '%' . $request->cari . '%');
        }

        $jenisPelanggaran = $query->orderBy('poin', 'asc')->paginate(10);

        return view('jenis_pelanggaran.index', compact('jenisPelanggaran'));
    }

    /**
     * Tampilkan form create jenis pelanggaran.
     */
    public function create()
    {
        $kategori = KategoriPelanggaran::all();
        return view('jenis_pelanggaran.create', compact('kategori'));
    }

    /**
     * Simpan jenis pelanggaran baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_pelanggaran' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori_pelanggaran,id',
            'poin' => 'required|integer|min:0',
        ]);

        JenisPelanggaran::create($request->all());

        return redirect()->route('jenis-pelanggaran.index')
            ->with('success', 'Aturan pelanggaran berhasil ditambahkan!');
    }

    /**
     * Tampilkan form edit jenis pelanggaran.
     */
    public function edit($id)
    {
        $jenisPelanggaran = JenisPelanggaran::findOrFail($id);
        $kategori = KategoriPelanggaran::all();
        return view('jenis_pelanggaran.edit', compact('jenisPelanggaran', 'kategori'));
    }

    /**
     * Perbarui jenis pelanggaran.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_pelanggaran' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori_pelanggaran,id',
            'poin' => 'required|integer|min:0',
        ]);

        $jenisPelanggaran = JenisPelanggaran::findOrFail($id);
        $jenisPelanggaran->update($request->all());

        return redirect()->route('jenis-pelanggaran.index')
            ->with('success', 'Aturan pelanggaran berhasil diperbarui!');
    }

    /**
     * Hapus jenis pelanggaran.
     * Proteksi: tidak bisa hapus jika sudah tercatat di riwayat siswa.
     */
    public function destroy($id)
    {
        $jenisPelanggaran = JenisPelanggaran::findOrFail($id);

        // Cek apakah pelanggaran ini sudah pernah dipakai di riwayat
        if ($jenisPelanggaran->riwayatPelanggaran()->exists()) {
            return back()->with('error', 'Gagal hapus! Pelanggaran ini sudah tercatat di riwayat siswa. (Hanya boleh diedit)');
        }

        $jenisPelanggaran->delete();
        return redirect()->route('jenis-pelanggaran.index')->with('success', 'Aturan berhasil dihapus.');
    }
}