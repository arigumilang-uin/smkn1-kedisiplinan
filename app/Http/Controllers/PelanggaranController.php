<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\JenisPelanggaran;
use App\Models\RiwayatPelanggaran;
use App\Models\TindakLanjut;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\KategoriPelanggaran; // [BARU] Import Kategori
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PelanggaranController extends Controller
{
    public function create()
    {
        // Load data pendukung Filter Siswa
        $jurusan = Jurusan::all();
        $kelas = Kelas::orderBy('nama_kelas')->get();
        $siswa = Siswa::with('kelas.jurusan')->orderBy('nama_siswa')->get();

        // [UPDATE] Load Pelanggaran & Kategori
        $kategori = KategoriPelanggaran::all();
        
        // Kita urutkan pelanggaran berdasarkan kategorinya agar rapi di dropdown
        $jenisPelanggaran = JenisPelanggaran::with('kategoriPelanggaran')
            ->orderBy('kategori_id')
            ->orderBy('nama_pelanggaran')
            ->get();

        return view('pelanggaran.create', [
            'daftarSiswa' => $siswa,
            'daftarPelanggaran' => $jenisPelanggaran,
            'jurusan' => $jurusan,
            'kelas' => $kelas,
            'kategori' => $kategori // [BARU] Kirim data kategori ke View
        ]);
    }

    // ... (Method store dan jalankanRulesEngine TETAP SAMA, tidak ada perubahan) ...
    
    public function store(Request $request)
    {
        // Copy-paste method store dari file sebelumnya
        $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'jenis_pelanggaran_id' => 'required|exists:jenis_pelanggaran,id',
            'tanggal_kejadian' => 'required|date',
            'bukti_foto' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'keterangan' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $pathFoto = $request->file('bukti_foto')->store('bukti_pelanggaran', 'public');

            RiwayatPelanggaran::create([
                'siswa_id' => $request->siswa_id,
                'jenis_pelanggaran_id' => $request->jenis_pelanggaran_id,
                'guru_pencatat_user_id' => Auth::id(),
                'tanggal_kejadian' => $request->tanggal_kejadian,
                'keterangan' => $request->keterangan,
                'bukti_foto_path' => $pathFoto,
            ]);
            
            $this->jalankanRulesEngine($request->siswa_id, $request->jenis_pelanggaran_id);

            return redirect()->route('pelanggaran.create')
                ->with('success', 'Pelanggaran berhasil dicatat! Sistem poin telah diperbarui.');
        });
    }

    private function jalankanRulesEngine($siswaId, $pelanggaranIdBaru)
    {
        $siswa = Siswa::find($siswaId);
        $pelanggaranBaru = JenisPelanggaran::find($pelanggaranIdBaru);
        $namaPelanggaran = strtolower($pelanggaranBaru->nama_pelanggaran);
        $poinBaru = $pelanggaranBaru->poin;

        $totalPoin = RiwayatPelanggaran::where('siswa_id', $siswaId)
            ->join('jenis_pelanggaran', 'riwayat_pelanggaran.jenis_pelanggaran_id', '=', 'jenis_pelanggaran.id')
            ->sum('jenis_pelanggaran.poin');

        $frekuensi = RiwayatPelanggaran::where('siswa_id', $siswaId)
            ->where('jenis_pelanggaran_id', $pelanggaranIdBaru)->count();

         $tipeSurat = null;
         $pemicu = null;
         $status = 'Baru';

         if (str_contains($namaPelanggaran, 'atribut') && $frekuensi == 10) { $tipeSurat = 'Surat 1'; $pemicu = "Atribut (10x)"; }
         elseif (str_contains($namaPelanggaran, 'alfa') && $frekuensi == 4) { $tipeSurat = 'Surat 1'; $pemicu = "Alfa (4x)"; }
         
         if (!$tipeSurat) {
             if ($poinBaru >= 100 && $poinBaru <= 500) { $tipeSurat = 'Surat 2'; $pemicu = "Pelanggaran Berat"; }
             elseif ($poinBaru > 500) { $tipeSurat = 'Surat 3'; $status = 'Menunggu Persetujuan'; $pemicu = "Sangat Berat"; }
         }

         if ($totalPoin >= 55 && $totalPoin <= 300) {
             if (!$tipeSurat || $tipeSurat == 'Surat 1') { $tipeSurat = 'Surat 2'; $pemicu = "Akumulasi $totalPoin"; }
         } elseif ($totalPoin > 300) {
             $tipeSurat = 'Surat 3'; $status = 'Menunggu Persetujuan'; $pemicu = "Akumulasi Kritis $totalPoin";
         }

         if ($tipeSurat) {
            $sanksi = "Pemanggilan Orang Tua ($tipeSurat)";
            $kasusAktif = TindakLanjut::with('suratPanggilan')->where('siswa_id', $siswaId)
                ->whereIn('status', ['Baru', 'Menunggu Persetujuan', 'Disetujui', 'Ditangani'])->latest()->first();

            if (!$kasusAktif) {
                $tl = TindakLanjut::create(['siswa_id' => $siswaId, 'pemicu' => $pemicu, 'sanksi_deskripsi' => $sanksi, 'status' => $status]);
                $tl->suratPanggilan()->create(['nomor_surat' => 'DRAFT/'.rand(100,999), 'tipe_surat' => $tipeSurat, 'tanggal_surat' => now()]);
            } else {
                 $levelLama = (int) filter_var($kasusAktif->suratPanggilan->tipe_surat ?? '0', FILTER_SANITIZE_NUMBER_INT);
                 $levelBaru = (int) filter_var($tipeSurat, FILTER_SANITIZE_NUMBER_INT);
                 if ($levelBaru > $levelLama) {
                     $kasusAktif->update(['pemicu' => $pemicu . " (Eskalasi)", 'sanksi_deskripsi' => $sanksi, 'status' => $status]);
                     if($kasusAktif->suratPanggilan) $kasusAktif->suratPanggilan()->update(['tipe_surat' => $tipeSurat]);
                 }
            }
         }
    }
}