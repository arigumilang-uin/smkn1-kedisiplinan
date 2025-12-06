# Quick Reference: Frequency-Based Point System

## 🎯 Core Concepts (Must Remember!)

### 1. Point Accumulation
```
❌ OLD: Poin setiap kali pelanggaran
✅ NEW: Poin hanya saat threshold tercapai

Contoh Alfa 4x:
OLD: 25 + 25 + 25 + 25 = 100 poin ❌
NEW: 0 + 0 + 25 (threshold 1-3) + 25 (threshold 4) = 50 poin ✅
```

### 2. Surat Pemanggilan
```
Trigger: Pelanggaran + Frekuensi + Sanksi "Panggilan orang tua"
Tipe: Berdasarkan jumlah pembina (1=Surat 1, 2=Surat 2, dst)

❌ TIDAK trigger dari akumulasi poin!
```

### 3. Pembinaan Internal
```
Berdasarkan: Total poin akumulasi
Output: Rekomendasi siapa yang konseling
❌ TIDAK trigger surat pemanggilan!

0-50: Wali Kelas
55-100: Wali Kelas + Kaprodi
105-300: Wali Kelas + Kaprodi + Waka
305-500: Semua Pembina
>500: Dikembalikan ke orang tua
```

---

## 📊 Database Schema

### Table: `pelanggaran_frequency_rules`
```sql
id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
jenis_pelanggaran_id    BIGINT UNSIGNED NOT NULL (FK)
frequency_min           INT NOT NULL
frequency_max           INT NULL
poin                    INT NOT NULL
sanksi_description      TEXT NOT NULL
trigger_surat           BOOLEAN DEFAULT FALSE
pembina_roles           JSON NOT NULL
display_order           INT DEFAULT 0
created_at              TIMESTAMP NULL
updated_at              TIMESTAMP NULL
```

### Column: `jenis_pelanggaran.has_frequency_rules`
```sql
has_frequency_rules     BOOLEAN DEFAULT FALSE
```

### Role: `Waka Sarana`
```sql
INSERT INTO roles (nama_role) VALUES ('Waka Sarana');
```

---

## 🔧 Key Methods

### PelanggaranRulesEngine

#### 1. evaluateFrequencyRules()
```php
/**
 * Evaluasi frequency rules untuk satu siswa dan satu jenis pelanggaran.
 * Return: ['poin_ditambahkan' => int, 'surat_type' => string|null, 'sanksi' => string]
 */
private function evaluateFrequencyRules(int $siswaId, JenisPelanggaran $pelanggaran): array
```

**Logic:**
1. Hitung current frequency
2. Load frequency rules
3. Find matched rule untuk current frequency
4. Find matched rule untuk previous frequency
5. Compare: Jika rule berbeda → threshold baru → tambah poin
6. Return poin, surat type, sanksi

#### 2. tentukanTipeSuratTertinggi()
```php
/**
 * Tentukan tipe surat tertinggi dari array surat types.
 * ⚠️ TIDAK consider akumulasi poin!
 */
private function tentukanTipeSuratTertinggi(array $suratTypes): ?string
```

**Logic:**
1. Extract level dari surat types
2. Find max level
3. Return "Surat {maxLevel}"

#### 3. getPembinaanInternalRekomendasi()
```php
/**
 * Rekomendasi pembina untuk konseling berdasarkan akumulasi poin.
 * ⚠️ TIDAK trigger surat!
 */
private function getPembinaanInternalRekomendasi(int $totalPoin): array
```

**Logic:**
1. Check range poin
2. Return ['pembina_roles' => array, 'keterangan' => string]

---

## 📝 Tata Tertib Reference

### Pelanggaran dengan Frequency Rules

#### Ringan
- **Atribut**: 1-9 (0 poin) → 10+ (5 poin, Surat 1)
- **Terlambat apel**: 1-9 (0 poin) → 10+ (5 poin, Surat 1)
- **Terlambat tidak apel**: 1-3 (0 poin) → 4+ (10 poin, Surat 1)
- **Sholat**: 1-4 (0 poin) → 5+ (10 poin)

#### Sedang
- **Alfa**: 1-3 (25 poin) → 4+ (25 poin, Surat 1)
- **Cabut**: 1 (25 poin) → 2+ (25 poin, Surat 1)
- **Kegiatan**: 1+ (25 poin)
- **HP**: 1-2 (0 poin) → 3+ (25 poin, Surat 1)
- **Fasilitas**: 1+ (50 poin)

#### Berat (Immediate, No Frequency Rules)
- **Merokok**: 1x → 100 poin, Surat 2
- **Bullying**: 1x → 100 poin, Surat 2
- **Senjata tajam**: 1x → 100 poin, Surat 2
- **Mencuri**: 1x → 100 poin, Surat 2
- **Berkelahi**: 1x → 100 poin, Surat 2
- **Pemerasan**: 1x → 100 poin, Surat 2
- **Melawan guru**: 1x → 200 poin, Surat 2
- **Alkohol**: 1x → 200 poin, Surat 3
- **Porno aksi**: 1x → 200 poin, Surat 3
- **Narkoba**: 1x → 501 poin, Surat 4
- **Kejahatan polisi**: 1x → 501 poin, Surat 4

---

## 🧪 Test Scenarios

### Scenario 1: Alfa 4x (Frequency Rules)
```
Pencatatan 1: Alfa → Freq 1 → Rule 1 (1-3) → +0 poin → Total: 0
Pencatatan 2: Alfa → Freq 2 → Rule 1 (1-3) → +0 poin → Total: 0
Pencatatan 3: Alfa → Freq 3 → Rule 1 (1-3) → +25 poin → Total: 25
Pencatatan 4: Alfa → Freq 4 → Rule 2 (4+) → +25 poin, Surat 1 → Total: 50

Expected: 50 poin, Surat 1
```

### Scenario 2: Atribut 10x (Frequency Rules)
```
Pencatatan 1-9: Atribut → Freq 1-9 → Rule 1 (1-9) → +0 poin → Total: 0
Pencatatan 10: Atribut → Freq 10 → Rule 2 (10+) → +5 poin, Surat 1 → Total: 5

Expected: 5 poin, Surat 1
```

### Scenario 3: Merokok 1x (Immediate)
```
Pencatatan 1: Merokok → No frequency rules → +100 poin, Surat 2 → Total: 100

Expected: 100 poin, Surat 2
```

### Scenario 4: Akumulasi 60 poin (Pembinaan Internal)
```
Siswa punya total 60 poin akumulasi
→ Rekomendasi: Wali Kelas + Kaprodi (konseling)
→ TIDAK ADA SURAT otomatis

Expected: Rekomendasi pembinaan, NO surat
```

### Scenario 5: Alfa 4x + Akumulasi 60 poin
```
Siswa punya 35 poin
Pencatatan Alfa ke-4:
→ Freq 4 → Rule 2 (4+) → +25 poin, Surat 1
→ Total poin: 35 + 25 = 60 poin
→ Rekomendasi pembinaan: Wali Kelas + Kaprodi

Expected: 
- Surat 1 dibuat (dari frequency rule)
- Rekomendasi pembinaan: Wali Kelas + Kaprodi (dari akumulasi)
- Kedua sistem berjalan independen
```

---

## 🚨 Common Mistakes

### ❌ Mistake 1: Trigger Surat dari Akumulasi
```php
// SALAH!
if ($totalPoin >= 55) {
    $this->buatSuratPemanggilan('Surat 2');
}
```

**Fix:**
```php
// BENAR!
$rekomendasi = $this->getPembinaanInternalRekomendasi($totalPoin);
// Hanya log rekomendasi, tidak buat surat
```

### ❌ Mistake 2: Tambah Poin Setiap Kali
```php
// SALAH!
foreach ($pelanggaranIds as $id) {
    $pelanggaran = JenisPelanggaran::find($id);
    $totalPoin += $pelanggaran->poin;
}
```

**Fix:**
```php
// BENAR!
foreach ($pelanggaranObjs as $pelanggaran) {
    if ($pelanggaran->usesFrequencyRules()) {
        $result = $this->evaluateFrequencyRules($siswaId, $pelanggaran);
        $totalPoin += $result['poin_ditambahkan'];
    } else {
        $totalPoin += $pelanggaran->poin;
    }
}
```

### ❌ Mistake 3: Tidak Check Threshold Baru
```php
// SALAH!
$matchedRule = $rules->first(fn($r) => $r->matchesFrequency($currentFreq));
return ['poin_ditambahkan' => $matchedRule->poin];
```

**Fix:**
```php
// BENAR!
$matchedRule = $rules->first(fn($r) => $r->matchesFrequency($currentFreq));
$previousRule = $rules->first(fn($r) => $r->matchesFrequency($currentFreq - 1));

if ($previousRule && $previousRule->id === $matchedRule->id) {
    return ['poin_ditambahkan' => 0]; // Masih di range yang sama
}

return ['poin_ditambahkan' => $matchedRule->poin]; // Threshold baru!
```

---

## 📁 File Locations

### Models
```
app/Models/PelanggaranFrequencyRule.php
app/Models/JenisPelanggaran.php (update)
```

### Services
```
app/Services/PelanggaranRulesEngine.php (major refactor)
```

### Controllers
```
app/Http/Controllers/FrequencyRulesController.php (new)
app/Http/Controllers/Dashboard/WakaSaranaDashboardController.php (new)
app/Http/Controllers/PelanggaranController.php (update)
```

### Views
```
resources/views/frequency-rules/index.blade.php (new)
resources/views/frequency-rules/show.blade.php (new)
resources/views/dashboards/waka_sarana.blade.php (new)
resources/views/pelanggaran/create.blade.php (update)
resources/views/layouts/app.blade.php (update sidebar)
```

### Migrations
```
database/migrations/2025_12_06_100000_create_pelanggaran_frequency_rules_table.php
database/migrations/2025_12_06_100001_add_has_frequency_rules_to_jenis_pelanggaran.php
database/migrations/2025_12_06_100002_add_waka_sarana_role.php
```

### Seeders
```
database/seeders/FrequencyRulesSeeder.php (new)
database/seeders/DatabaseSeeder.php (update)
```

### Routes
```
routes/web.php (add routes for frequency rules, waka sarana, API preview)
```

---

## 🔗 Quick Links

- **Full Requirements**: `requirements.md`
- **Technical Design**: `design.md`
- **Implementation Tasks**: `tasks.md`
- **Business Rules**: `TATA_TERTIB_REFERENCE.md`
- **Flow Diagrams**: `FLOW_DIAGRAM.md`
- **FAQ**: `FAQ.md`
- **Changelog**: `CHANGELOG.md`
- **Implementation Checklist**: `IMPLEMENTATION_CHECKLIST.md`

---

## 💡 Tips

1. **Always read design.md Section 4** sebelum coding service layer
2. **Always check TATA_TERTIB_REFERENCE.md** saat seeding data
3. **Always test dengan scenarios di atas** setelah implementasi
4. **Always backup database** sebelum migration
5. **Always check FLOW_DIAGRAM.md** jika bingung dengan flow

---

**Last Updated**: 2025-12-06  
**Version**: 2.0.0 (Planned)
