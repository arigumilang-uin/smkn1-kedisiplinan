<?php

namespace App\Http\Controllers\Pelanggaran;

use App\Http\Controllers\Controller;
use App\Services\Pelanggaran\PelanggaranService;
use App\Data\Pelanggaran\RiwayatPelanggaranData;
use App\Data\Pelanggaran\RiwayatPelanggaranFilterData;
use App\Http\Requests\Pelanggaran\CatatPelanggaranRequest;
use App\Http\Requests\Pelanggaran\UpdatePelanggaranRequest;
use App\Http\Requests\Pelanggaran\FilterRiwayatRequest;
use App\Models\JenisPelanggaran;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\RiwayatPelanggaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Riwayat Pelanggaran Controller - Clean Architecture Pattern
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
 * - TIDAK BOLEH ada manipulasi data
 * - Target: < 20 baris per method
 * - TIDAK BOLEH inject RulesEngine (hanya Service!)
 */
class RiwayatPelanggaranController extends Controller
{
    /**
     * Inject PelanggaranService via constructor.
     * 
     * CRITICAL: Inject SERVICE, bukan RulesEngine!
     * Controller tidak perlu tahu tentang internal business logic.
     *
     * @param PelanggaranService $pelanggaranService
     */
    public function __construct(
        private PelanggaranService $pelanggaranService
    ) {}

    /**
     * Tampilkan daftar riwayat pelanggaran dengan filter.
     * 
     * ALUR:
     * 1. Validasi filter (via FilterRiwayatRequest)
     * 2. Convert ke RiwayatPelanggaranFilterData (DTO)
     * 3. Panggil service
     * 4. Return view
     */
    public function index(FilterRiwayatRequest $request): View
    {
        // Convert validated request data ke DTO
        $filters = RiwayatPelanggaranFilterData::from($request->getFilterData());

        // Panggil service untuk get filtered riwayat
        $riwayat = $this->pelanggaranService->getFilteredRiwayat($filters);

        // Data untuk dropdown filter (TODO: pindah ke service jika kompleks)
        $allJurusan = Jurusan::all();
        $allKelas = Kelas::all();
        $allPelanggaran = JenisPelanggaran::orderBy('nama_pelanggaran')->get();

        return view('riwayat.index', compact('riwayat', 'allJurusan', 'allKelas', 'allPelanggaran'));
    }

    /**
     * Tampilkan form create pelanggaran.
     */
    public function create(): View
    {
        $jenisPelanggaran = $this->pelanggaranService->getActiveJenisPelanggaran();
        
        return view('riwayat.create', compact('jenisPelanggaran'));
    }

    /**
     * Simpan pelanggaran baru.
     * 
     * ALUR:
     * 1. Validasi (via CatatPelanggaranRequest)
     * 2. Handle file upload jika ada
     * 3. Convert ke RiwayatPelanggaranData (DTO)
     * 4. Panggil service->catatPelanggaran()
     * 5. Redirect dengan success message
     */
    public function store(CatatPelanggaranRequest $request): RedirectResponse
    {
        // Handle file upload
        $buktiFotoPath = null;
        if ($request->hasFile('bukti_foto')) {
            $buktiFotoPath = $request->file('bukti_foto')
                ->store('bukti_pelanggaran', 'public');
        }

        // Convert validated request ke DTO dengan combined datetime
        $riwayatData = RiwayatPelanggaranData::from([
            'id' => null,
            'siswa_id' => $request->siswa_id,
            'jenis_pelanggaran_id' => $request->jenis_pelanggaran_id,
            'guru_pencatat_user_id' => $request->guru_pencatat_user_id,
            'tanggal_kejadian' => $request->getCombinedDateTime(),
            'keterangan' => $request->keterangan,
            'bukti_foto_path' => $buktiFotoPath,
        ]);

        // Panggil service
        // Service akan: simpan data + panggil RulesEngine + buat tindak lanjut jika perlu
        $this->pelanggaranService->catatPelanggaran($riwayatData);

        return redirect()
            ->route('riwayat.index')
            ->with('success', 'Pelanggaran berhasil dicatat.');
    }

    /**
     * Tampilkan form edit pelanggaran.
     */
    public function edit(int $id): View
    {
        // Untuk show, kita perlu model lengkap dengan relationships
        // UpdatePelanggaranRequest akan handle authorization
        $riwayat = RiwayatPelanggaran::with(['siswa', 'jenisPelanggaran'])
            ->findOrFail($id);
        
        $jenisPelanggaran = $this->pelanggaranService->getActiveJenisPelanggaran();

        return view('riwayat.edit', compact('riwayat', 'jenisPelanggaran'));
    }

    /**
     * Update pelanggaran.
     * 
     * ALUR:
     * 1. Validasi + Authorization (via UpdatePelanggaranRequest)
     * 2. Handle file upload jika ada
     * 3. Convert ke RiwayatPelanggaranData (DTO)
     * 4. Panggil service->updatePelanggaran()
     * 5. Redirect dengan success message
     */
    public function update(UpdatePelanggaranRequest $request, int $id): RedirectResponse
    {
        // Get existing record untuk old file path
        $existingRiwayat = RiwayatPelanggaran::findOrFail($id);
        $oldBuktiFotoPath = $existingRiwayat->bukti_foto_path;

        // Handle file upload
        $buktiFotoPath = null;
        if ($request->hasFile('bukti_foto')) {
            $buktiFotoPath = $request->file('bukti_foto')
                ->store('bukti_pelanggaran', 'public');
        }

        // Convert validated request ke DTO
        $riwayatData = RiwayatPelanggaranData::from([
            'id' => $id,
            'siswa_id' => $existingRiwayat->siswa_id,
            'jenis_pelanggaran_id' => $request->jenis_pelanggaran_id,
            'guru_pencatat_user_id' => $existingRiwayat->guru_pencatat_user_id,
            'tanggal_kejadian' => $request->getCombinedDateTime(),
            'keterangan' => $request->keterangan,
            'bukti_foto_path' => $buktiFotoPath ?? $oldBuktiFotoPath,
        ]);

        // Panggil service
        // Service akan: update data + reconcile tindak lanjut (poin/frekuensi berubah)
        $this->pelanggaranService->updatePelanggaran(
            $id,
            $riwayatData,
            $buktiFotoPath ? $oldBuktiFotoPath : null
        );

        return redirect()
            ->route('riwayat.index')
            ->with('success', 'Riwayat pelanggaran berhasil diperbarui.');
    }

    /**
     * Hapus pelanggaran.
     * 
     * ALUR:
     * 1. Authorization manual (karena tidak ada FormRequest untuk delete)
     * 2. Panggil service->deletePelanggaran()
     * 3. Service akan reconcile tindak lanjut
     * 4. Redirect dengan success message
     */
    public function destroy(int $id): RedirectResponse
    {
        $riwayat = RiwayatPelanggaran::findOrFail($id);

        // Manual authorization check (same logic as UpdatePelanggaranRequest)
        $user = auth()->user();
        
        if (!$user->hasRole('Operator Sekolah')) {
            if ($riwayat->guru_pencatat_user_id !== $user->id) {
                abort(403, 'AKSES DITOLAK: Anda hanya dapat mengelola riwayat yang Anda catat.');
            }

            if ($riwayat->created_at) {
                $created = \Carbon\Carbon::parse($riwayat->created_at);
                if (\Carbon\Carbon::now()->greaterThan($created->copy()->addDays(3))) {
                    abort(403, 'Batas waktu hapus telah lewat (lebih dari 3 hari sejak pencatatan).');
                }
            }
        }

        // Panggil service
        // Service akan: hapus file + hapus record + reconcile (deleteIfNoSurat = true)
        $this->pelanggaranService->deletePelanggaran(
            $id,
            $riwayat->siswa_id,
            $riwayat->bukti_foto_path
        );

        return redirect()
            ->route('riwayat.index')
            ->with('success', 'Riwayat pelanggaran berhasil dihapus.');
    }

    /**
     * Tampilkan riwayat yang dicatat oleh user saat ini.
     * Operator dapat lihat semua, role lain hanya yang mereka catat.
     */
    public function myIndex(FilterRiwayatRequest $request): View
    {
        $user = auth()->user();

        // Build filter dengan scope berdasarkan role
        $filterData = $request->getFilterData();
        
        // Non-operator hanya lihat yang mereka catat
        if (!$user->hasRole('Operator Sekolah')) {
            $filterData['guru_pencatat_user_id'] = $user->id;
        }

        $filters = RiwayatPelanggaranFilterData::from($filterData);
        $riwayat = $this->pelanggaranService->getFilteredRiwayat($filters);

        return view('riwayat.my_index', compact('riwayat'));
    }
}
