<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\TindakLanjut;
use App\Models\SuratPanggilan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    /**
     * List semua kasus menunggu persetujuan
     */
    public function index()
    {
        $kasusMenunggu = TindakLanjut::with(['siswa.kelas', 'suratPanggilan'])
            ->where('status', 'Menunggu Persetujuan')
            ->orderBy('created_at', 'asc')
            ->paginate(10);

        return view('kepala_sekolah.approvals.index', [
            'kasusMenunggu' => $kasusMenunggu,
        ]);
    }

    /**
     * Show detail kasus untuk approval
     */
    public function show(TindakLanjut $tindakLanjut)
    {
        // Load relations
        $tindakLanjut->load(['siswa.kelas.jurusan', 'siswa.waliMurid', 'suratPanggilan', 'user']);

        return view('kepala_sekolah.approvals.show', [
            'kasus' => $tindakLanjut,
        ]);
    }

    /**
     * Process approval (approve or reject)
     */
    public function process(Request $request, TindakLanjut $tindakLanjut)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'catatan_kepala_sekolah' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $action = $request->input('action');
        $catatan = $request->input('catatan_kepala_sekolah');

        DB::beginTransaction();
        try {
            if ($action === 'approve') {
                $tindakLanjut->update([
                    'status' => 'Disetujui',
                    'catatan_kepala_sekolah' => $catatan,
                    'disetujui_oleh' => $user->id,
                    'tanggal_disetujui' => now(),
                ]);

                // Log activity
                activity('approval')
                    ->performedBy($user)
                    ->withProperties(['kasus_id' => $tindakLanjut->id, 'action' => 'approve'])
                    ->log("Menyetujui kasus: {$tindakLanjut->siswa->nama_siswa}");

                return redirect()->route('kepala-sekolah.approvals.index')
                    ->with('success', 'Kasus telah disetujui.');
            } else {
                $tindakLanjut->update([
                    'status' => 'Ditolak',
                    'catatan_kepala_sekolah' => $catatan,
                    'disetujui_oleh' => $user->id,
                    'tanggal_disetujui' => now(),
                ]);

                // Log activity
                activity('approval')
                    ->performedBy($user)
                    ->withProperties(['kasus_id' => $tindakLanjut->id, 'action' => 'reject'])
                    ->log("Menolak kasus: {$tindakLanjut->siswa->nama_siswa}");

                return redirect()->route('kepala-sekolah.approvals.index')
                    ->with('warning', 'Kasus telah ditolak.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }

        DB::commit();
    }
}


