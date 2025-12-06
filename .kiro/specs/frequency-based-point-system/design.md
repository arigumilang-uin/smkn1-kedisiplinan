# Design Document: Frequency-Based Point System

## 1. Overview

Dokumen ini menjelaskan desain teknis untuk implementasi **Frequency-Based Point System** yang akan mengubah fundamental logic dari Rules Engine. Sistem baru akan memberikan poin berdasarkan threshold frekuensi, bukan setiap kali pelanggaran tercatat.

### Key Changes

1. **Database**: Tambah tabel `pelanggaran_frequency_rules` dan kolom `has_frequency_rules` di `jenis_pelanggaran`
2. **Models**: Tambah model `PelanggaranFrequencyRule` dengan relasi ke `JenisPelanggaran`
3. **Service**: Refactor `PelanggaranRulesEngine` untuk frequency-based evaluation
4. **Role**: Tambah role baru `Waka Sarana` dengan dashboard khusus
5. **UI**: Tambah halaman management frequency rules untuk Operator

---

## 2. Database Schema

### 2.1. New Table: `pelanggaran_frequency_rules`

Tabel ini menyimpan aturan threshold frekuensi untuk setiap jenis pelanggaran.

```sql
CREATE TABLE pelanggaran_frequency_rules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    jenis_pelanggaran_id BIGINT UNSIGNED NOT NULL,
    frequency_min INT NOT NULL,
    frequency_max INT NULL,
    poin INT NOT NULL,
    sanksi_description TEXT NOT NULL,
    trigger_surat BOOLEAN DEFAULT FALSE,
    pembina_roles JSON NOT NULL,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (jenis_pelanggaran_id) REFERENCES jenis_pelanggaran(id) ON DELETE CASCADE,
    INDEX idx_jenis_pelanggaran (jenis_pelanggaran_id),
    INDEX idx_display_order (display_order)
);
```

**Kolom Explanation:**
- `frequency_min`: Threshold minimum (contoh: 1, 4, 10)
- `frequency_max`: Threshold maximum (NULL jika open-ended, contoh: 3, 9, NULL)
- `poin`: Poin yang diberikan saat threshold tercapai
- `sanksi_description`: Deskripsi sanksi (contoh: "Pembinaan ditempat", "Panggilan orang tua dan denda...")
- `trigger_surat`: TRUE jika sanksi memicu surat pemanggilan
- `pembina_roles`: JSON array role pembina (contoh: ["Wali Kelas"], ["Wali Kelas", "Kaprodi"])
- `display_order`: Urutan tampilan di UI

**Contoh Data:**
```json
// Alfa (Sedang)
{
  "jenis_pelanggaran_id": 8,
  "frequency_min": 1,
  "frequency_max": 3,
  "poin": 25,
  "sanksi_description": "Pembinaan",
  "trigger_surat": false,
  "pembina_roles": ["Wali Kelas"],
  "display_order": 1
}
{
  "jenis_pelanggaran_id": 8,
  "frequency_min": 4,
  "frequency_max": null,
  "poin": 25,
  "sanksi_description": "Panggilan orang tua dan denda membawa 1 buah pot bunga diameter 30 cm (Berlaku kelipatan)",
  "trigger_surat": true,
  "pembina_roles": ["Wali Kelas"],
  "display_order": 2
}
```

### 2.2. Update Table: `jenis_pelanggaran`

Tambah kolom flag untuk menandai pelanggaran yang menggunakan frequency rules.

```sql
ALTER TABLE jenis_pelanggaran 
ADD COLUMN has_frequency_rules BOOLEAN DEFAULT FALSE AFTER poin;
```

**Logic:**
- `has_frequency_rules = TRUE`: Gunakan frequency-based evaluation
- `has_frequency_rules = FALSE`: Gunakan immediate point accumulation (backward compatibility)

### 2.3. Update Table: `roles`

Tambah role baru untuk Waka Sarana.

```sql
INSERT INTO roles (nama_role) VALUES ('Waka Sarana');
```

---

## 3. Models & Relationships

### 3.1. New Model: `PelanggaranFrequencyRule`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PelanggaranFrequencyRule extends Model
{
    protected $table = 'pelanggaran_frequency_rules';
    
    protected $fillable = [
        'jenis_pelanggaran_id',
        'frequency_min',
        'frequency_max',
        'poin',
        'sanksi_description',
        'trigger_surat',
        'pembina_roles',
        'display_order',
    ];
    
    protected $casts = [
        'trigger_surat' => 'boolean',
        'pembina_roles' => 'array',
    ];
    
    // Relasi ke JenisPelanggaran
    public function jenisPelanggaran(): BelongsTo
    {
        return $this->belongsTo(JenisPelanggaran::class, 'jenis_pelanggaran_id');
    }
    
    // Helper: Cek apakah frekuensi masuk dalam range rule ini
    public function matchesFrequency(int $frequency): bool
    {
        if ($this->frequency_max === null) {
            return $frequency >= $this->frequency_min;
        }
        return $frequency >= $this->frequency_min && $frequency <= $this->frequency_max;
    }
    
    // Helper: Tentukan tipe surat berdasarkan pembina
    public function getSuratType(): ?string
    {
        if (!$this->trigger_surat) {
            return null;
        }
        
        $pembinaCount = count($this->pembina_roles);
        
        // Surat 1: Wali Kelas
        if ($pembinaCount === 1 && in_array('Wali Kelas', $this->pembina_roles)) {
            return 'Surat 1';
        }
        
        // Surat 2: Wali Kelas + Kaprodi
        if ($pembinaCount === 2 && 
            in_array('Wali Kelas', $this->pembina_roles) && 
            in_array('Kaprodi', $this->pembina_roles)) {
            return 'Surat 2';
        }
        
        // Surat 3: Wali Kelas + Kaprodi + Waka
        if ($pembinaCount === 3 && 
            in_array('Wali Kelas', $this->pembina_roles) && 
            in_array('Kaprodi', $this->pembina_roles) && 
            in_array('Waka Kesiswaan', $this->pembina_roles)) {
            return 'Surat 3';
        }
        
        // Surat 4: Semua pembina (Narkoba, Kejahatan)
        if ($pembinaCount === 4) {
            return 'Surat 4';
        }
        
        return null;
    }
}
```

### 3.2. Update Model: `JenisPelanggaran`

Tambah relasi ke `PelanggaranFrequencyRule`.

```php
// Tambahkan di app/Models/JenisPelanggaran.php

/**
 * Relasi Opsional: SATU JenisPelanggaran MEMILIKI BANYAK FrequencyRules.
 */
public function frequencyRules(): HasMany
{
    return $this->hasMany(PelanggaranFrequencyRule::class, 'jenis_pelanggaran_id')
                ->orderBy('display_order');
}

/**
 * Helper: Cek apakah pelanggaran ini menggunakan frequency rules
 */
public function usesFrequencyRules(): bool
{
    return $this->has_frequency_rules === true;
}
```

---

## 4. Service Layer Refactoring

### 4.1. PelanggaranRulesEngine - New Logic

Refactor service untuk support frequency-based evaluation.

#### 4.1.1. New Method: `evaluateFrequencyRules()`

```php
/**
 * Evaluasi frequency rules untuk satu siswa dan satu jenis pelanggaran.
 * 
 * @param int $siswaId
 * @param JenisPelanggaran $pelanggaran
 * @return array ['poin_ditambahkan' => int, 'surat_type' => string|null, 'sanksi' => string]
 */
private function evaluateFrequencyRules(int $siswaId, JenisPelanggaran $pelanggaran): array
{
    // Hitung frekuensi total pelanggaran ini untuk siswa
    $currentFrequency = RiwayatPelanggaran::where('siswa_id', $siswaId)
        ->where('jenis_pelanggaran_id', $pelanggaran->id)
        ->count();
    
    // Ambil semua frequency rules untuk pelanggaran ini
    $rules = $pelanggaran->frequencyRules;
    
    if ($rules->isEmpty()) {
        // Fallback: tidak ada rules, gunakan poin langsung
        return [
            'poin_ditambahkan' => $pelanggaran->poin,
            'surat_type' => null,
            'sanksi' => 'Pembinaan',
        ];
    }
    
    // Cari rule yang match dengan frekuensi saat ini
    $matchedRule = $rules->first(function($rule) use ($currentFrequency) {
        return $rule->matchesFrequency($currentFrequency);
    });
    
    if (!$matchedRule) {
        // Tidak ada rule yang match, tidak ada poin
        return [
            'poin_ditambahkan' => 0,
            'surat_type' => null,
            'sanksi' => 'Belum mencapai threshold',
        ];
    }
    
    // Cek apakah threshold ini sudah pernah tercapai sebelumnya
    $previousFrequency = $currentFrequency - 1;
    $previousRule = $rules->first(function($rule) use ($previousFrequency) {
        return $rule->matchesFrequency($previousFrequency);
    });
    
    // Jika rule sekarang sama dengan rule sebelumnya, berarti masih di range yang sama
    // Tidak perlu tambah poin lagi
    if ($previousRule && $previousRule->id === $matchedRule->id) {
        return [
            'poin_ditambahkan' => 0,
            'surat_type' => null,
            'sanksi' => $matchedRule->sanksi_description,
        ];
    }
    
    // Threshold baru tercapai! Tambahkan poin
    return [
        'poin_ditambahkan' => $matchedRule->poin,
        'surat_type' => $matchedRule->getSuratType(),
        'sanksi' => $matchedRule->sanksi_description,
    ];
}
```

#### 4.1.2. Update Method: `processBatch()`

```php
public function processBatch(int $siswaId, array $pelanggaranIds): void
{
    $siswa = Siswa::find($siswaId);
    if (!$siswa) return;

    $pelanggaranObjs = JenisPelanggaran::with('frequencyRules')
        ->whereIn('id', $pelanggaranIds)
        ->get();
    
    if ($pelanggaranObjs->isEmpty()) return;

    $totalPoinBaru = 0;
    $suratTypes = [];
    $sanksiList = [];

    // Evaluasi setiap pelanggaran
    foreach ($pelanggaranObjs as $pelanggaran) {
        if ($pelanggaran->usesFrequencyRules()) {
            // Gunakan frequency-based evaluation
            $result = $this->evaluateFrequencyRules($siswaId, $pelanggaran);
            $totalPoinBaru += $result['poin_ditambahkan'];
            
            if ($result['surat_type']) {
                $suratTypes[] = $result['surat_type'];
            }
            
            $sanksiList[] = $result['sanksi'];
        } else {
            // Fallback: immediate accumulation (backward compatibility)
            $totalPoinBaru += $pelanggaran->poin;
        }
    }

    // Tentukan tipe surat tertinggi (HANYA dari frequency rules)
    $tipeSurat = $this->tentukanTipeSuratTertinggi($suratTypes);

    // Buat/update TindakLanjut jika diperlukan
    if ($tipeSurat) {
        $pemicu = implode(', ', array_unique($sanksiList));
        $status = $tipeSurat === 'Surat 3' || $tipeSurat === 'Surat 4' 
            ? 'Menunggu Persetujuan' 
            : 'Baru';
        
        $this->buatAtauUpdateTindakLanjut($siswaId, $tipeSurat, $pemicu, $status);
    }
    
    // OPTIONAL: Log rekomendasi pembinaan internal (tidak trigger surat)
    // Ini hanya untuk informasi, tidak membuat TindakLanjut
    $totalPoinAkumulasi = $this->hitungTotalPoinAkumulasi($siswaId);
    $pembinaanRekomendasi = $this->getPembinaanInternalRekomendasi($totalPoinAkumulasi);
    // Log atau simpan rekomendasi untuk dashboard/reporting
}
```

#### 4.1.3. New Method: `tentukanTipeSuratTertinggi()`

```php
/**
 * Tentukan tipe surat tertinggi dari array surat types.
 * Prioritas: Surat 4 > Surat 3 > Surat 2 > Surat 1
 * 
 * CATATAN PENTING: Akumulasi poin TIDAK trigger surat otomatis!
 * Surat HANYA dari frequency rules yang memiliki trigger_surat = TRUE
 */
private function tentukanTipeSuratTertinggi(array $suratTypes): ?string
{
    if (empty($suratTypes)) {
        // Tidak ada surat dari frequency rules
        return null;
    }

    // Extract level dari surat types
    $levels = array_map(function($surat) {
        return (int) filter_var($surat, FILTER_SANITIZE_NUMBER_INT);
    }, $suratTypes);

    $maxLevel = max($levels);

    return $maxLevel > 0 ? "Surat {$maxLevel}" : null;
}
```

#### 4.1.4. New Method: `getPembinaanInternalRekomendasi()`

```php
/**
 * Tentukan rekomendasi pembina untuk pembinaan internal berdasarkan akumulasi poin.
 * CATATAN: Ini HANYA rekomendasi konseling, TIDAK trigger surat pemanggilan.
 * 
 * @param int $totalPoin Total poin akumulasi siswa
 * @return array ['pembina_roles' => array, 'keterangan' => string]
 */
private function getPembinaanInternalRekomendasi(int $totalPoin): array
{
    // 0-50: Wali Kelas (konseling ringan)
    if ($totalPoin >= 0 && $totalPoin <= 50) {
        return [
            'pembina_roles' => ['Wali Kelas'],
            'keterangan' => 'Pembinaan ringan, konseling'
        ];
    }
    
    // 55-100: Wali Kelas + Kaprodi (monitoring ketat)
    if ($totalPoin >= 55 && $totalPoin <= 100) {
        return [
            'pembina_roles' => ['Wali Kelas', 'Kaprodi'],
            'keterangan' => 'Pembinaan sedang, monitoring ketat'
        ];
    }
    
    // 105-300: Wali Kelas + Kaprodi + Waka (pembinaan intensif)
    if ($totalPoin >= 105 && $totalPoin <= 300) {
        return [
            'pembina_roles' => ['Wali Kelas', 'Kaprodi', 'Waka Kesiswaan'],
            'keterangan' => 'Pembinaan intensif, evaluasi berkala'
        ];
    }
    
    // 305-500: Wali Kelas + Kaprodi + Waka + Kepsek (pembinaan kritis)
    if ($totalPoin >= 305 && $totalPoin <= 500) {
        return [
            'pembina_roles' => ['Wali Kelas', 'Kaprodi', 'Waka Kesiswaan', 'Kepala Sekolah'],
            'keterangan' => 'Pembinaan kritis, pertemuan dengan orang tua'
        ];
    }
    
    // >500: Dikembalikan kepada orang tua
    if ($totalPoin > 500) {
        return [
            'pembina_roles' => ['Kepala Sekolah'],
            'keterangan' => 'Dikembalikan kepada orang tua, siswa tidak dapat melanjutkan'
        ];
    }
    
    return [
        'pembina_roles' => [],
        'keterangan' => 'Tidak ada pembinaan'
    ];
}
```

---

## 5. Role: Waka Sarana

### 5.1. Dashboard Controller

Buat controller baru untuk dashboard Waka Sarana.

```php
<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\RiwayatPelanggaran;
use App\Models\JenisPelanggaran;
use Illuminate\Support\Facades\Auth;

class WakaSaranaDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Ambil jenis pelanggaran "Merusak Fasilitas"
        $jenisFasilitas = JenisPelanggaran::where('nama_pelanggaran', 'LIKE', '%merusak%fasilitas%')
            ->orWhere('nama_pelanggaran', 'LIKE', '%mencoret%')
            ->pluck('id');
        
        // Statistik pelanggaran fasilitas
        $totalPelanggaranFasilitas = RiwayatPelanggaran::whereIn('jenis_pelanggaran_id', $jenisFasilitas)
            ->count();
        
        $pelanggaranBulanIni = RiwayatPelanggaran::whereIn('jenis_pelanggaran_id', $jenisFasilitas)
            ->whereMonth('tanggal_kejadian', now()->month)
            ->whereYear('tanggal_kejadian', now()->year)
            ->count();
        
        // Riwayat pelanggaran fasilitas terbaru
        $riwayatTerbaru = RiwayatPelanggaran::with(['siswa.kelas.jurusan', 'jenisPelanggaran', 'guruPencatat'])
            ->whereIn('jenis_pelanggaran_id', $jenisFasilitas)
            ->orderBy('tanggal_kejadian', 'desc')
            ->limit(10)
            ->get();
        
        // Riwayat yang dicatat oleh Waka Sarana sendiri
        $riwayatSaya = RiwayatPelanggaran::with(['siswa.kelas.jurusan', 'jenisPelanggaran'])
            ->where('guru_pencatat_user_id', $user->id)
            ->orderBy('tanggal_kejadian', 'desc')
            ->limit(5)
            ->get();
        
        return view('dashboards.waka_sarana', compact(
            'totalPelanggaranFasilitas',
            'pelanggaranBulanIni',
            'riwayatTerbaru',
            'riwayatSaya'
        ));
    }
}
```

### 5.2. Routes

Tambah route untuk Waka Sarana.

```php
// Di routes/web.php

Route::get('/dashboard/waka-sarana', [WakaSaranaDashboardController::class, 'index'])
    ->middleware('role:Waka Sarana')
    ->name('dashboard.waka_sarana');
```

### 5.3. Middleware Update

Update `CheckRole` middleware untuk support role Waka Sarana (sudah otomatis support karena menggunakan RoleService).

### 5.4. Access Control

Waka Sarana memiliki permission yang sama dengan Guru:
- Bisa catat semua jenis pelanggaran
- Hanya bisa edit/hapus riwayat yang dia catat sendiri
- Tidak bisa lihat semua riwayat (hanya yang dia catat + pelanggaran fasilitas)

---

## 6. UI Components

### 6.1. Halaman Management Frequency Rules (Operator)

Buat halaman baru untuk Operator mengelola frequency rules.

**Route:**
```php
Route::get('/frequency-rules', [FrequencyRulesController::class, 'index'])
    ->middleware('role:Operator Sekolah')
    ->name('frequency-rules.index');

Route::get('/frequency-rules/{jenisPelanggaran}', [FrequencyRulesController::class, 'show'])
    ->middleware('role:Operator Sekolah')
    ->name('frequency-rules.show');

Route::post('/frequency-rules/{jenisPelanggaran}', [FrequencyRulesController::class, 'store'])
    ->middleware('role:Operator Sekolah')
    ->name('frequency-rules.store');

Route::put('/frequency-rules/{rule}', [FrequencyRulesController::class, 'update'])
    ->middleware('role:Operator Sekolah')
    ->name('frequency-rules.update');

Route::delete('/frequency-rules/{rule}', [FrequencyRulesController::class, 'destroy'])
    ->middleware('role:Operator Sekolah')
    ->name('frequency-rules.destroy');
```

**View Structure:**
- List semua jenis pelanggaran dengan toggle "Has Frequency Rules"
- Untuk pelanggaran dengan frequency rules, tampilkan tabel rules
- Form untuk tambah/edit rule dengan validasi threshold overlap
- Preview dampak perubahan rules

### 6.2. Real-time Frequency Display (Guru/Wali Kelas)

Update form pencatatan pelanggaran untuk menampilkan:
- Frekuensi saat ini untuk siswa yang dipilih
- Warning jika threshold akan tercapai
- Preview poin yang akan ditambahkan
- Preview sanksi yang akan ditrigger

**Implementation:**
- AJAX call saat siswa dan jenis pelanggaran dipilih
- Endpoint: `GET /api/pelanggaran/preview-frequency?siswa_id={id}&jenis_pelanggaran_id={id}`
- Response: `{ frequency: 3, next_threshold: 4, poin_akan_ditambahkan: 25, sanksi: "..." }`

---

## 7. Data Migration Strategy

### 7.1. Migration Files

1. `2025_12_06_create_pelanggaran_frequency_rules_table.php`
2. `2025_12_06_add_has_frequency_rules_to_jenis_pelanggaran.php`
3. `2025_12_06_add_waka_sarana_role.php`
4. `2025_12_06_seed_frequency_rules_from_tata_tertib.php`

### 7.2. Seeder: Populate Frequency Rules

Buat seeder untuk populate frequency rules dari tata tertib yang user berikan.

**Contoh untuk Alfa:**
```php
$alfa = JenisPelanggaran::where('nama_pelanggaran', 'LIKE', '%Alfa%')->first();

if ($alfa) {
    $alfa->update(['has_frequency_rules' => true]);
    
    // Rule 1: Frekuensi 1-3, poin 25, pembinaan
    PelanggaranFrequencyRule::create([
        'jenis_pelanggaran_id' => $alfa->id,
        'frequency_min' => 1,
        'frequency_max' => 3,
        'poin' => 25,
        'sanksi_description' => 'Pembinaan',
        'trigger_surat' => false,
        'pembina_roles' => ['Wali Kelas'],
        'display_order' => 1,
    ]);
    
    // Rule 2: Frekuensi 4+, poin 25, panggilan orang tua
    PelanggaranFrequencyRule::create([
        'jenis_pelanggaran_id' => $alfa->id,
        'frequency_min' => 4,
        'frequency_max' => null,
        'poin' => 25,
        'sanksi_description' => 'Panggilan orang tua dan denda membawa 1 buah pot bunga diameter 30 cm (Berlaku kelipatan)',
        'trigger_surat' => true,
        'pembina_roles' => ['Wali Kelas'],
        'display_order' => 2,
    ]);
}
```

### 7.3. Backward Compatibility

Pelanggaran yang TIDAK memiliki frequency rules (`has_frequency_rules = FALSE`) akan tetap menggunakan logic lama:
- Poin diberikan langsung setiap kali pelanggaran tercatat
- Tidak ada threshold evaluation

Ini memastikan pelanggaran berat (frekuensi = 1) tetap berfungsi seperti sebelumnya.

---

## 8. Testing Strategy

### 8.1. Unit Tests

1. `PelanggaranFrequencyRule::matchesFrequency()` - Test range matching
2. `PelanggaranFrequencyRule::getSuratType()` - Test surat type determination
3. `PelanggaranRulesEngine::evaluateFrequencyRules()` - Test frequency evaluation
4. `PelanggaranRulesEngine::tentukanTipeSuratTertinggi()` - Test surat prioritization

### 8.2. Integration Tests

1. Test pencatatan pelanggaran dengan frequency rules
2. Test threshold tercapai → poin ditambahkan
3. Test threshold belum tercapai → poin tidak ditambahkan
4. Test multiple threshold untuk satu pelanggaran
5. Test eskalasi surat berdasarkan akumulasi poin
6. Test backward compatibility untuk pelanggaran tanpa frequency rules

### 8.3. Manual Testing Scenarios

1. **Scenario 1: Alfa 4x**
   - Catat Alfa 1x → 0 poin
   - Catat Alfa 2x → 0 poin
   - Catat Alfa 3x → +25 poin (threshold 1-3 tercapai)
   - Catat Alfa 4x → +25 poin (threshold 4 tercapai, trigger Surat 1)
   - Total: 50 poin

2. **Scenario 2: Atribut 10x**
   - Catat Atribut 1-9x → 0 poin
   - Catat Atribut 10x → +5 poin (threshold 10 tercapai, trigger Surat 1)
   - Total: 5 poin

3. **Scenario 3: Merokok 1x (Pelanggaran Berat)**
   - Catat Merokok 1x → +100 poin (langsung, trigger Surat 2)
   - Total: 100 poin

4. **Scenario 4: Akumulasi Poin → Eskalasi Surat**
   - Siswa punya 60 poin → Surat 2 (akumulasi sedang)
   - Siswa punya 150 poin → Surat 3 (akumulasi kritis)
   - Siswa punya 400 poin → Surat 4 (akumulasi sangat kritis)

---

## 9. Performance Considerations

### 9.1. Database Indexing

- Index pada `jenis_pelanggaran_id` di `pelanggaran_frequency_rules`
- Index pada `display_order` untuk sorting
- Index pada `siswa_id` dan `jenis_pelanggaran_id` di `riwayat_pelanggaran` (sudah ada)

### 9.2. Caching Strategy

- Cache frequency rules per jenis pelanggaran (TTL: 1 jam)
- Cache frekuensi siswa per pelanggaran (invalidate saat ada pencatatan baru)
- Cache total poin akumulasi siswa (invalidate saat ada perubahan)

### 9.3. Query Optimization

- Eager load `frequencyRules` saat evaluasi batch
- Gunakan `whereIn()` untuk batch processing
- Minimize database queries dengan caching

---

## 10. Security Considerations

### 10.1. Authorization

- Hanya Operator yang bisa manage frequency rules
- Waka Sarana hanya bisa edit/hapus riwayat yang dia catat sendiri
- Validasi threshold overlap untuk mencegah data inconsistency

### 10.2. Data Integrity

- Foreign key constraints untuk cascade delete
- Validasi `frequency_min <= frequency_max`
- Validasi `pembina_roles` harus array valid
- Audit trail untuk perubahan frequency rules

---

## 11. Rollback Plan

Jika terjadi masalah setelah deployment:

1. **Rollback Database:**
   - Drop tabel `pelanggaran_frequency_rules`
   - Remove kolom `has_frequency_rules` dari `jenis_pelanggaran`
   - Remove role `Waka Sarana`

2. **Rollback Code:**
   - Revert `PelanggaranRulesEngine` ke versi sebelumnya
   - Remove `PelanggaranFrequencyRule` model
   - Remove Waka Sarana dashboard & routes

3. **Data Recovery:**
   - Backup database sebelum migration
   - Restore dari backup jika diperlukan

---

## 12. Success Metrics

1. **Functional:**
   - Poin akumulasi akurat sesuai tata tertib
   - Surat pemanggilan trigger sesuai threshold
   - Waka Sarana dapat fokus pada pelanggaran fasilitas

2. **Performance:**
   - Response time pencatatan pelanggaran < 500ms
   - Dashboard load time < 1s
   - Frequency evaluation < 100ms

3. **User Experience:**
   - Operator dapat manage frequency rules dengan mudah
   - Guru dapat melihat preview poin sebelum submit
   - Waka Sarana dapat monitor pelanggaran fasilitas dengan efektif

---

## 13. Next Steps

Setelah design document ini disetujui, lanjut ke **Tasks Document** yang akan breakdown implementasi menjadi step-by-step tasks.
