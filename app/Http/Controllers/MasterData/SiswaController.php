<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Services\Siswa\SiswaService;
use App\Data\Siswa\SiswaData;
use App\Data\Siswa\SiswaFilterData;
use App\Http\Requests\Siswa\CreateSiswaRequest;
use App\Http\Requests\Siswa\UpdateSiswaRequest;
use App\Http\Requests\Siswa\FilterSiswaRequest;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Siswa Controller - Clean Architecture Pattern
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
 * - TIDAK BOLEH ada query database
 * - TIDAK BOLEH ada manipulasi data
 * - Target: < 20 baris per method
 */
class SiswaController extends Controller
{
    /**
     * Inject SiswaService via constructor.
     *
     * @param SiswaService $siswaService
     */
    public function __construct(
        private SiswaService $siswaService
    ) {}

    /**
     * Tampilkan daftar siswa dengan filter.
     * 
     * ALUR:
     * 1. Validasi filter (via FilterSiswaRequest)
     * 2. Convert ke SiswaFilterData (DTO)
     * 3. Panggil service
     * 4. Return view
     */
    public function index(FilterSiswaRequest $request): View
    {
        // Convert validated request data ke DTO
        $filters = SiswaFilterData::from($request->getFilterData());

        // Panggil service untuk get filtered siswa
        $siswa = $this->siswaService->getFilteredSiswa($filters);

        // Data untuk dropdown filter
        $allKelas = Kelas::orderBy('nama_kelas')->get();
        $allJurusan = Jurusan::orderBy('nama_jurusan')->get();

        return view('siswa.index', compact('siswa', 'allKelas', 'allJurusan'));
    }

    /**
     * Tampilkan form create siswa.
     */
    public function create(): View
    {
        $kelas = Kelas::orderBy('nama_kelas')->get();
        
        $waliMurid = User::whereHas('role', function($q) {
            $q->where('nama_role', 'Wali Murid');
        })->orderBy('nama')->get();

        return view('siswa.create', compact('kelas', 'waliMurid'));
    }

    /**
     * Simpan siswa baru.
     * 
     * ALUR:
     * 1. Validasi (via CreateSiswaRequest)
     * 2. Convert ke SiswaData (DTO)
     * 3. Panggil service->createSiswa()
     * 4. Flash credentials jika wali dibuat
     * 5. Redirect dengan success message
     */
    public function store(CreateSiswaRequest $request): RedirectResponse
    {
        // Convert validated request ke DTO
        $siswaData = SiswaData::from($request->validated());

        // Panggil service dengan DTO + primitive boolean
        $result = $this->siswaService->createSiswa(
            $siswaData,
            $request->boolean('create_wali')
        );

        // Flash credentials ke session jika wali dibuat (untuk ditampilkan)
        if ($result['wali_credentials']) {
            session()->flash('wali_created', $result['wali_credentials']);
        }

        return redirect()
            ->route('siswa.index')
            ->with('success', 'Data Siswa Berhasil Ditambahkan');
    }

    /**
     * Tampilkan detail siswa.
     */
    public function show(int $id): View
    {
        // Untuk show, kita perlu model lengkap dengan relationships
        // TODO: Buat method getSiswaDetail di service jika perlu logic tambahan
        $siswa = \App\Models\Siswa::with([
            'kelas.jurusan.kaprodi',
            'kelas.waliKelas',
            'waliMurid',
            'riwayatPelanggaran.jenisPelanggaran.kategoriPelanggaran',
            'riwayatPelanggaran.guruPencatat',
            'tindakLanjut'
        ])->findOrFail($id);

        // Hitung total poin
        $totalPoin = $siswa->riwayatPelanggaran->sum(function($riwayat) {
            return $riwayat->jenisPelanggaran->poin ?? 0;
        });

        return view('siswa.show', compact('siswa', 'totalPoin'));
    }

    /**
     * Tampilkan form edit siswa.
     */
    public function edit(int $id): View
    {
        $siswa = \App\Models\Siswa::findOrFail($id);
        $kelas = Kelas::orderBy('nama_kelas')->get();
        
        $waliMurid = User::whereHas('role', function($q) {
            $q->where('nama_role', 'Wali Murid');
        })->orderBy('nama')->get();

        return view('siswa.edit', compact('siswa', 'kelas', 'waliMurid'));
    }

    /**
     * Update siswa.
     * 
     * ALUR:
     * 1. Validasi (via UpdateSiswaRequest - role-based rules)
     * 2. Convert ke SiswaData (DTO)
     * 3. Panggil service->updateSiswa() dengan flag isWaliKelas
     * 4. Redirect dengan success message
     */
    public function update(UpdateSiswaRequest $request, int $id): RedirectResponse
    {
        // Convert validated request ke DTO
        $siswaData = SiswaData::from($request->validated());

        // Panggil service dengan DTO + flag role
        $this->siswaService->updateSiswa(
            $id,
            $siswaData,
            $request->isWaliKelas()
        );

        return redirect()
            ->route('siswa.index')
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    /**
     * Hapus siswa.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->siswaService->deleteSiswa($id);

        return redirect()
            ->route('siswa.index')
            ->with('success', 'Data Siswa Berhasil Dihapus');
    }
}
