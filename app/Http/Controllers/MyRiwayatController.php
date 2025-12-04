<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RiwayatPelanggaran;
use App\Models\JenisPelanggaran;
use App\Services\PelanggaranRulesEngine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * MyRiwayatController
 *
 * Controller untuk melihat, mengubah, dan menghapus riwayat pelanggaran
 * yang dicatat oleh user yang sedang login (pencatat).
 * Hanya pencatat (guru_pencatat_user_id) yang dapat mengedit/hapus record ini.
 */
class MyRiwayatController extends Controller
{
    private PelanggaranRulesEngine $rulesEngine;

    public function __construct(PelanggaranRulesEngine $rulesEngine)
    {
        $this->rulesEngine = $rulesEngine;
    }
    /**
     * Tampilkan daftar riwayat yang dicatat oleh user saat ini.
     */
    public function index(Request $request)
    {
        $userId = Auth::id();

        $query = RiwayatPelanggaran::with(['siswa.kelas', 'jenisPelanggaran'])
            ->where('guru_pencatat_user_id', $userId)
            ->orderBy('tanggal_kejadian', 'desc');

        // Optional filters (tanggal range)
        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_kejadian', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_kejadian', '<=', $request->end_date);
        }

        $riwayat = $query->paginate(20)->withQueryString();

        return view('riwayat.my_index', compact('riwayat'));
    }

    /**
     * Tampilkan form edit untuk riwayat milik user.
     */
    public function edit($id)
    {
        $record = RiwayatPelanggaran::findOrFail($id);
        $this->authorizeOwner($record);

        $jenis = JenisPelanggaran::orderBy('nama_pelanggaran')->get();
        return view('riwayat.edit_my', ['r' => $record, 'jenis' => $jenis]);
    }

    /**
     * Update record riwayat (only owner).
     */
    public function update(Request $request, $id)
    {
        $record = RiwayatPelanggaran::findOrFail($id);
        $this->authorizeOwner($record);

        $request->validate([
            'jenis_pelanggaran_id' => 'required|exists:jenis_pelanggaran,id',
            'tanggal_kejadian' => 'required|date',
            'jam_kejadian' => 'nullable|date_format:H:i',
            'keterangan' => 'nullable|string',
            'bukti_foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        DB::transaction(function () use ($request, $record) {
            // combine date+time
            $time = $request->jam_kejadian ?? Carbon::now()->format('H:i');
            try {
                $dt = Carbon::createFromFormat('Y-m-d H:i', $request->tanggal_kejadian . ' ' . $time)->toDateTimeString();
            } catch (\Exception $e) {
                $dt = Carbon::parse($request->tanggal_kejadian . ' ' . $time)->toDateTimeString();
            }

            $data = [
                'jenis_pelanggaran_id' => $request->jenis_pelanggaran_id,
                'tanggal_kejadian' => $dt,
                'keterangan' => $request->keterangan,
            ];

            if ($request->hasFile('bukti_foto')) {
                // remove old file if exists
                if ($record->bukti_foto_path) {
                    Storage::disk('public')->delete($record->bukti_foto_path);
                }
                $path = $request->file('bukti_foto')->store('bukti_pelanggaran', 'public');
                $data['bukti_foto_path'] = $path;
            }

            $record->update($data);
        });

        // Rekalkulasi tindak lanjut untuk siswa terkait (edit: jangan hapus kasus otomatis)
        $this->rulesEngine->reconcileForSiswa($record->siswa_id, false);

        return redirect()->route('my-riwayat.index')->with('success', 'Riwayat pelanggaran berhasil diperbarui.');
    }

    /**
     * Hapus riwayat (only owner).
     */
    public function destroy($id)
    {
        $record = RiwayatPelanggaran::findOrFail($id);
        $this->authorizeOwner($record);

        // remove file if exists
        if ($record->bukti_foto_path) {
            Storage::disk('public')->delete($record->bukti_foto_path);
        }

        $siswaId = $record->siswa_id;
        $record->delete();

        // Rekalkulasi tindak lanjut setelah penghapusan (hapus kasus jika tidak lagi perlu)
        $this->rulesEngine->reconcileForSiswa($siswaId, true);

        return redirect()->route('my-riwayat.index')->with('success', 'Riwayat pelanggaran berhasil dihapus.');
    }

    /**
     * Pastikan record dimiliki oleh user saat ini.
     */
    private function authorizeOwner(RiwayatPelanggaran $record): void
    {
        if ($record->guru_pencatat_user_id !== Auth::id()) {
            abort(403, 'AKSES DITOLAK: Anda hanya dapat mengelola riwayat yang Anda catat.');
        }

        // Batasi kemampuan edit/hapus sampai 3 hari sejak pencatatan
        $created = Carbon::parse($record->created_at);
        if (Carbon::now()->greaterThan($created->copy()->addDays(3))) {
            abort(403, 'Batas waktu edit/hapus telah lewat (lebih dari 3 hari sejak pencatatan).');
        }
    }
}
