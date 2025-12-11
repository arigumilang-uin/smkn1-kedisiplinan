<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Services\Siswa\SiswaService;
use App\Data\Siswa\SiswaData;
use App\Data\Siswa\SiswaFilterData;
use App\Http\Requests\Siswa\CreateSiswaRequest;
use App\Http\Requests\Siswa\UpdateSiswaRequest;
use App\Http\Requests\Siswa\FilterSiswaRequest;
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
     * 3. Apply role-based auto-filters (Kaprodi/Wali Kelas)
     * 4. Panggil service untuk data siswa
     * 5. Panggil service untuk master data filter
     * 6. Return view
     */
    public function index(FilterSiswaRequest $request): View
    {
        // Convert validated request data ke DTO
        $filterData = $request->getFilterData();
        
        // AUTO-FILTER berdasarkan role
        $user = auth()->user();
        $role = $user->effectiveRoleName() ?? $user->role?->nama_role;
        
        // Kaprodi: filter by assigned jurusan
        if ($role === 'Kaprodi' && !isset($filterData['jurusan_id'])) {
            $jurusanKaprodi = \App\Models\Jurusan::where('kaprodi_user_id', $user->id)->first();
            if ($jurusanKaprodi) {
                $filterData['jurusan_id'] = $jurusanKaprodi->id;
            }
        }
        
        // Wali Kelas: filter by assigned kelas
        if ($role === 'Wali Kelas' && !isset($filterData['kelas_id'])) {
            $kelasWali = \App\Models\Kelas::where('wali_kelas_user_id', $user->id)->first();
            if ($kelasWali) {
                $filterData['kelas_id'] = $kelasWali->id;
            }
        }
        
        // Convert to DTO with role-based filters applied
        $filters = SiswaFilterData::from($filterData);

        // Panggil service untuk get filtered siswa
        $siswa = $this->siswaService->getFilteredSiswa($filters);

        // Panggil service untuk master data dropdown filter
        $allKelas = $this->siswaService->getAllKelasForFilter();
        $allJurusan = $this->siswaService->getAllJurusanForFilter();

        return view('siswa.index', compact('siswa', 'allKelas', 'allJurusan'));
    }

    /**
     * Tampilkan form create siswa.
     */
    public function create(): View
    {
        $kelas = $this->siswaService->getAllKelas();
        $waliMurid = $this->siswaService->getAvailableWaliMurid();

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
     * 
     * ALUR:
     * 1. Panggil service untuk get detail siswa (dengan eager loading)
     * 2. Service menghitung total poin (business logic)
     * 3. Return view dengan data
     */
    public function show(int $id): View
    {
        // Panggil service untuk get detail lengkap siswa
        $result = $this->siswaService->getSiswaDetail($id);

        return view('siswa.show', [
            'siswa' => $result['siswa'],
            'totalPoin' => $result['totalPoin'],
            'pembinaanRekomendasi' => $result['pembinaanRekomendasi'],
        ]);
    }

    /**
     * Tampilkan form edit siswa.
     * 
     * ALUR:
     * 1. Panggil service untuk get siswa
     * 2. Panggil service untuk master data (kelas, wali murid)
     * 3. Return view
     */
    public function edit(int $id): View
    {
        $siswa = $this->siswaService->getSiswaForEdit($id);
        $kelas = $this->siswaService->getAllKelas();
        $waliMurid = $this->siswaService->getAvailableWaliMurid();

        return view('siswa.edit', compact('siswa', 'kelas', 'waliMurid'));
    }

    /**
     * Update siswa.
     * 
     * ALUR:
     * 1. Validasi (via UpdateSiswaRequest - role-based rules)
     * 2. Convert ke SiswaData (DTO) - handle partial data for Wali Kelas
     * 3. Panggil service->updateSiswa() dengan flag isWaliKelas
     * 4. Redirect dengan success message
     */
    public function update(UpdateSiswaRequest $request, int $id): RedirectResponse
    {
        // For Wali Kelas: merge validated data with existing siswa data
        // Because Wali Kelas only validates nomor_hp_wali_murid
        if ($request->isWaliKelas()) {
            $existingSiswa = $this->siswaService->getSiswaForEdit($id);
            $mergedData = array_merge(
                $existingSiswa->toArray(),
                $request->validated()
            );
            $siswaData = SiswaData::from($mergedData);
        } else {
            // For Operator: use validated data directly
            $siswaData = SiswaData::from($request->validated());
        }

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

    /**
     * Tampilkan form bulk create siswa.
     * 
     * ALUR:
     * 1. Panggil service untuk master data (kelas)
     * 2. Return view bulk_create
     */
    public function bulkCreate(): View
    {
        $kelas = $this->siswaService->getAllKelas();

        return view('siswa.bulk_create', compact('kelas'));
    }

    /**
     * Proses bulk create siswa dari CSV/Excel.
     * 
     * ALUR:
     * 1. Validasi kelas_id dan input type
     * 2. Parse data dari file (CSV/XLSX) atau manual table
     * 3. Validasi setiap baris
     * 4. Panggil service untuk bulk insert
     * 5. Return hasil (success/errors)
     */
    public function bulkStore(\Illuminate\Http\Request $request): RedirectResponse
    {
        try {
            // Validasi input dasar
            $request->validate([
                'kelas_id' => 'required|exists:kelas,id',
                'bulk_file' => 'nullable|file|mimes:csv,txt,xlsx|max:2048',
                'bulk_data' => 'nullable|string',
            ]);

            $kelasId = $request->input('kelas_id');
            $createWaliAll = $request->boolean('create_wali_all');
            $rows = [];

            // Parse data dari file atau manual input
            if ($request->hasFile('bulk_file')) {
                $rows = $this->parseFileUpload($request->file('bulk_file'));
            } elseif ($request->filled('bulk_data')) {
                $rows = $this->parseManualData($request->input('bulk_data'));
            } else {
                return redirect()
                    ->back()
                    ->with('error', 'Silakan upload file atau isi tabel manual.');
            }

            // Validasi dan filter rows yang valid
            $validRows = [];
            $errors = [];
            $seenNisns = []; // Track NISNs in current batch
            
            foreach ($rows as $index => $row) {
                $lineNumber = $index + 1;
                
                // Validasi NISN dan Nama (required)
                if (empty($row['nisn']) || empty($row['nama'])) {
                    $errors[] = "Baris {$lineNumber}: NISN dan Nama harus diisi";
                    continue;
                }
                
                // Validate NISN format (10 digits)
                if (!preg_match('/^\d{10}$/', $row['nisn'])) {
                    $errors[] = "Baris {$lineNumber}: NISN harus 10 digit angka";
                    continue;
                }
                
                // Check duplicate NISN in current batch
                if (isset($seenNisns[$row['nisn']])) {
                    $errors[] = "Baris {$lineNumber}: NISN {$row['nisn']} duplicate dengan baris {$seenNisns[$row['nisn']]}";
                    continue;
                }
                
                // Check duplicate NISN in database
                if (\App\Models\Siswa::where('nisn', $row['nisn'])->exists()) {
                    $errors[] = "Baris {$lineNumber}: NISN {$row['nisn']} sudah terdaftar di database";
                    continue;
                }
                
                // Mark this NISN as seen
                $seenNisns[$row['nisn']] = $lineNumber;
                
                $validRows[] = $row;
            }

            // Jika tidak ada row yang valid
            if (empty($validRows)) {
                return redirect()
                    ->back()
                    ->with('error', 'Tidak ada data siswa yang valid untuk diproses.')
                    ->with('bulk_errors', $errors);
            }

            // Proses bulk create via service
            $result = $this->siswaService->bulkCreateSiswa($validRows, $kelasId, $createWaliAll);

            // Prepare success message
            $successCount = $result['success_count'];
            $message = "Berhasil menambahkan {$successCount} siswa.";
            
            if (!empty($errors)) {
                $message .= " Beberapa baris dilewati karena error.";
            }

            return redirect()
                ->route('siswa.index')
                ->with('success', $message)
                ->with('bulk_errors', $errors)
                ->with('wali_credentials', $result['wali_credentials'] ?? []);
                
        } catch (\Exception $e) {
            \Log::error('Bulk create siswa error: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'Gagal memproses bulk create: ' . $e->getMessage());
        }
    }

    /**
     * Parse uploaded CSV/XLSX file.
     */
    private function parseFileUpload($file): array
    {
        $rows = [];
        $extension = $file->getClientOriginalExtension();
        
        if ($extension === 'csv' || $extension === 'txt') {
            // Parse CSV
            $handle = fopen($file->getRealPath(), 'r');
            $header = fgetcsv($handle); // Skip header
            
            while (($data = fgetcsv($handle)) !== false) {
                if (count($data) >= 2) {
                    $rows[] = [
                        'nisn' => trim($data[0] ?? ''),
                        'nama' => trim($data[1] ?? ''),
                        'nomor_hp_wali_murid' => trim($data[2] ?? ''),
                    ];
                }
            }
            fclose($handle);
        } elseif ($extension === 'xlsx') {
            // For XLSX, we need a library. For now, show error message
            throw new \Exception('Format XLSX memerlukan library tambahan. Gunakan format CSV atau input manual.');
        }
        
        return $rows;
    }

    /**
     * Parse manual table data from textarea.
     */
    private function parseManualData(string $data): array
    {
        $rows = [];
        $lines = explode("\n", $data);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Support both semicolon and tab delimiter
            $parts = preg_split('/[;\t]/', $line);
            
            if (count($parts) >= 2) {
                $rows[] = [
                    'nisn' => trim($parts[0] ?? ''),
                    'nama' => trim($parts[1] ?? ''),
                    'nomor_hp_wali_murid' => trim($parts[2] ?? ''),
                ];
            }
        }
        
        return $rows;
    }
}
