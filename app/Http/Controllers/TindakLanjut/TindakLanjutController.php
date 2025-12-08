<?php

namespace App\Http\Controllers\TindakLanjut;

use App\Http\Controllers\Controller;
use App\Services\TindakLanjut\TindakLanjutService;
use App\Data\TindakLanjut\TindakLanjutData;
use App\Data\TindakLanjut\TindakLanjutFilterData;
use App\Http\Requests\TindakLanjut\CreateTindakLanjutRequest;
use App\Http\Requests\TindakLanjut\UpdateTindakLanjutRequest;
use App\Models\TindakLanjut;
use App\Models\Siswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Tindak Lanjut Controller - Clean Architecture Pattern
 * 
 * PERAN: Kurir (Courier)
 * - Menerima HTTP Request
 * - Validasi (via FormRequest)
 * - Convert ke DTO
 * - Panggil Service
 * - Return Response
 * 
 * ATURAN:
 * - TIDAK BOLEH ada business logic
 * - TIDAK BOLEH ada query database complex
 * - Target: < 20 baris per method
 */
class TindakLanjutController extends Controller
{
    /**
     * Inject TindakLanjutService via constructor.
     *
     * @param TindakLanjutService $tindakLanjutService
     */
    public function __construct(
        private TindakLanjutService $tindakLanjutService
    ) {}

    /**
     * Tampilkan daftar tindak lanjut dengan filter.
     */
    public function index(Request $request): View
    {
        // Build filter data
        $filters = TindakLanjutFilterData::from([
            'siswa_id' => $request->input('siswa_id'),
            'kelas_id' => $request->input('kelas_id'),
            'jurusan_id' => $request->input('jurusan_id'),
            'status' => $request->input('status') ? \App\Enums\StatusTindakLanjut::from($request->input('status')) : null,
            'pending_approval_only' => $request->boolean('pending_approval'),
            'active_only' => $request->boolean('active_only'),
            'perPage' => $request->input('perPage', 20),
        ]);

        $tindakLanjut = $this->tindakLanjutService->getFilteredTindakLanjut($filters);

        return view('tindak_lanjut.index', compact('tindakLanjut'));
    }

    /**
     * Tampilkan form create tindak lanjut.
     */
    public function create(): View
    {
        $siswa = Siswa::with('kelas')->orderBy('nama_siswa')->get();
        
        return view('tindak_lanjut.create', compact('siswa'));
    }

    /**
     * Simpan tindak lanjut baru.
     */
    public function store(CreateTindakLanjutRequest $request): RedirectResponse
    {
        // Convert validated request ke DTO
        $tindakLanjutData = TindakLanjutData::from([
            'id' => null,
            'siswa_id' => $request->siswa_id,
            'pemicu' => $request->pemicu,
            'sanksi_deskripsi' => $request->sanksi_deskripsi,
            'denda_deskripsi' => $request->denda_deskripsi,
            'status' => \App\Enums\StatusTindakLanjut::from($request->status),
            'tanggal_tindak_lanjut' => $request->tanggal_tindak_lanjut,
            'penyetuju_user_id' => $request->penyetuju_user_id,
        ]);

        // Panggil service
        $this->tindakLanjutService->createTindakLanjut($tindakLanjutData);

        return redirect()
            ->route('tindak-lanjut.index')
            ->with('success', 'Tindak lanjut berhasil dibuat.');
    }

    /**
     * Tampilkan detail tindak lanjut.
     */
    public function show(int $id): View
    {
        $tindakLanjut = TindakLanjut::with([
            'siswa.kelas.jurusan',
            'penyetuju',
            'suratPanggilan'
        ])->findOrFail($id);

        return view('tindak_lanjut.show', compact('tindakLanjut'));
    }

    /**
     * Tampilkan form edit tindak lanjut.
     */
    public function edit(int $id): View
    {
        $tindakLanjut = TindakLanjut::with('siswa')->findOrFail($id);
        
        return view('tindak_lanjut.edit', compact('tindakLanjut'));
    }

    /**
     * Update tindak lanjut.
     */
    public function update(UpdateTindakLanjutRequest $request, int $id): RedirectResponse
    {
        $existingTindakLanjut = TindakLanjut::findOrFail($id);

        // Convert validated request ke DTO
        $tindakLanjutData = TindakLanjutData::from([
            'id' => $id,
            'siswa_id' => $existingTindakLanjut->siswa_id,
            'pemicu' => $request->pemicu,
            'sanksi_deskripsi' => $request->sanksi_deskripsi,
            'denda_deskripsi' => $request->denda_deskripsi,
            'status' => \App\Enums\StatusTindakLanjut::from($request->status),
            'tanggal_tindak_lanjut' => $request->tanggal_tindak_lanjut,
            'penyetuju_user_id' => $request->penyetuju_user_id,
        ]);

        // Panggil service (will auto-restore siswa status if completed)
        $this->tindakLanjutService->updateTindakLanjut($id, $tindakLanjutData);

        return redirect()
            ->route('tindak-lanjut.index')
            ->with('success', 'Tindak lanjut berhasil diperbarui.');
    }

    /**
     * Approve tindak lanjut.
     */
    public function approve(int $id): RedirectResponse
    {
        $this->tindakLanjutService->approveTindakLanjut($id, auth()->id());

        return redirect()
            ->route('tindak-lanjut.show', $id)
            ->with('success', 'Tindak lanjut berhasil disetujui.');
    }

    /**
     * Reject tindak lanjut.
     */
    public function reject(Request $request, int $id): RedirectResponse
    {
        $reason = $request->input('reason', '');
        
        $this->tindakLanjutService->rejectTindakLanjut($id, auth()->id(), $reason);

        return redirect()
            ->route('tindak-lanjut.index')
            ->with('success', 'Tindak lanjut berhasil ditolak.');
    }

    /**
     * Complete tindak lanjut (mark as selesai).
     */
    public function complete(int $id): RedirectResponse
    {
        $this->tindakLanjutService->completeTindakLanjut($id);

        return redirect()
            ->route('tindak-lanjut.show', $id)
            ->with('success', 'Tindak lanjut berhasil diselesaikan.');
    }

    /**
     * Hapus tindak lanjut.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->tindakLanjutService->deleteTindakLanjut($id);

        return redirect()
            ->route('tindak-lanjut.index')
            ->with('success', 'Tindak lanjut berhasil dihapus.');
    }
}
