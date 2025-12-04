<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\JenisPelanggaran;
use App\Models\RiwayatPelanggaran;
use App\Models\TindakLanjut;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\KategoriPelanggaran;
use App\Services\PelanggaranRulesEngine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * PelanggaranController
 *
 * Controller untuk pengelolaan pencatatan pelanggaran siswa.
 * Fitur: form pencatatan, multi-select siswa/pelanggaran, otomatis rules engine.
 */
class PelanggaranController extends Controller
{
    private PelanggaranRulesEngine $rulesEngine;

    public function __construct(PelanggaranRulesEngine $rulesEngine)
    {
        $this->rulesEngine = $rulesEngine;
    }
    /**
     * Tampilkan form pencatatan pelanggaran.
     */
    public function create()
    {
        $jurusan = Jurusan::all();
        $kelas = Kelas::orderBy('nama_kelas')->get();
        $siswa = Siswa::with('kelas.jurusan')->orderBy('nama_siswa')->get();
        $kategori = KategoriPelanggaran::all();

        $jenisPelanggaran = JenisPelanggaran::with('kategoriPelanggaran')
            ->orderBy('kategori_id')
            ->orderBy('nama_pelanggaran')
            ->get();

        return view('pelanggaran.create', [
            'daftarSiswa' => $siswa,
            'daftarPelanggaran' => $jenisPelanggaran,
            'jurusan' => $jurusan,
            'kelas' => $kelas,
            'kategori' => $kategori
        ]);
    }

    /**
     * Simpan pelanggaran baru (multi-select siswa dan jenis pelanggaran).
     */
    public function store(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|array|min:1',
            'siswa_id.*' => 'exists:siswa,id',
            'jenis_pelanggaran_id' => 'required|array|min:1',
            'jenis_pelanggaran_id.*' => 'exists:jenis_pelanggaran,id',
            'tanggal_kejadian' => 'required|date',
            'jam_kejadian' => 'nullable|date_format:H:i',
            'bukti_foto' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'keterangan' => 'nullable|string',
        ]);

        $user = Auth::user();
        $siswaIds = $request->input('siswa_id', []);
        $pelanggaranIds = $request->input('jenis_pelanggaran_id', []);

        // Validasi permission: user boleh catat pelanggaran untuk siswa ini
        foreach ($siswaIds as $sid) {
            $siswa = Siswa::find($sid);
            if (!$user->canRecordFor($siswa)) {
                abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk mencatat pelanggaran untuk salah satu siswa terpilih.');
            }
        }

        return DB::transaction(function () use ($request, $siswaIds, $pelanggaranIds) {
            $pathFoto = $request->file('bukti_foto')->store('bukti_pelanggaran', 'public');
            $tanggalWaktu = $this->kombinasikanTanggalWaktu($request->tanggal_kejadian, $request->jam_kejadian);

            $created = 0;
            foreach ($siswaIds as $sid) {
                foreach ($pelanggaranIds as $pid) {
                    RiwayatPelanggaran::create([
                        'siswa_id' => $sid,
                        'jenis_pelanggaran_id' => $pid,
                        'guru_pencatat_user_id' => Auth::id(),
                        'tanggal_kejadian' => $tanggalWaktu,
                        'keterangan' => $request->keterangan,
                        'bukti_foto_path' => $pathFoto,
                    ]);
                    $created++;
                }

                // Jalankan rules engine batch per siswa (menghindari eskalasi ganda)
                $this->rulesEngine->processBatch($sid, $pelanggaranIds);
            }

            return redirect()->route('pelanggaran.create')
                ->with('success', "Pelanggaran berhasil dicatat: {$created} record. Sistem poin telah diperbarui.");
        });
    }

    /**
     * Gabungkan tanggal dan jam menjadi datetime string.
     */
    private function kombinasikanTanggalWaktu(string $tanggal, ?string $jam): string
    {
        $waktu = $jam ?? date('H:i');
        try {
            return Carbon::createFromFormat('Y-m-d H:i', $tanggal . ' ' . $waktu)->toDateTimeString();
        } catch (\Exception $e) {
            return Carbon::parse($tanggal . ' ' . $waktu)->toDateTimeString();
        }
    }


}