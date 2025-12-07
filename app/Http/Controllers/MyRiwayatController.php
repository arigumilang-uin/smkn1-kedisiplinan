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
     * Operator Sekolah dapat melihat SEMUA riwayat pelanggaran.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userId = Auth::id();

        $query = RiwayatPelanggaran::with(['siswa.kelas', 'jenisPelanggaran', 'guruPencatat']);

        // Operator Sekolah bisa lihat semua riwayat
        // Role lain hanya bisa lihat riwayat yang mereka catat sendiri
        if (!$user->hasRole('Operator Sekolah')) {
            $query->where('guru_pencatat_user_id', $userId);
        }

        $query->orderBy('tanggal_kejadian', 'desc');

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
    public function edit(Request $request, $id)
    {
        $record = RiwayatPelanggaran::findOrFail($id);
        $this->authorizeOwner($record);

        // Store return URL in session
        if ($request->has('return_url')) {
            session(['riwayat_return_url' => $request->return_url]);
        } elseif ($request->headers->get('referer')) {
            session(['riwayat_return_url' => $request->headers->get('referer')]);
        }

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

        // Redirect back to previous page or default to my-riwayat.index
        $redirectUrl = session('riwayat_return_url', route('my-riwayat.index'));
        session()->forget('riwayat_return_url');
        
        return redirect($redirectUrl)->with('success', 'Riwayat pelanggaran berhasil diperbarui.');
    }

    /**
     * Hapus riwayat (only owner).
     */
    public function destroy(Request $request, $id)
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

        // Redirect back to return_url or previous page or default to my-riwayat.index
        $redirectUrl = $request->input('return_url') 
                    ?? session('riwayat_return_url') 
                    ?? route('my-riwayat.index');
        session()->forget('riwayat_return_url');
        
        return redirect($redirectUrl)->with('success', 'Riwayat pelanggaran berhasil dihapus.');
    }

    /**
     * Pastikan record dimiliki oleh user saat ini.
     * Operator Sekolah bisa edit/hapus semua record tanpa batasan.
     */
    private function authorizeOwner(RiwayatPelanggaran $record): void
    {
        $user = Auth::user();

        // Operator Sekolah bisa edit/hapus semua record tanpa batasan waktu
        if ($user->hasRole('Operator Sekolah')) {
            return;
        }

        // Untuk role lain, hanya bisa edit/hapus record yang mereka catat sendiri
        if ($record->guru_pencatat_user_id !== Auth::id()) {
            abort(403, 'AKSES DITOLAK: Anda hanya dapat mengelola riwayat yang Anda catat.');
        }

        // Batasi kemampuan edit/hapus sampai 3 hari sejak pencatatan
        if ($record->created_at) {
            $created = Carbon::parse($record->created_at);
            if (Carbon::now()->greaterThan($created->copy()->addDays(3))) {
                abort(403, 'Batas waktu edit/hapus telah lewat (lebih dari 3 hari sejak pencatatan).');
            }
        }
    }
}
