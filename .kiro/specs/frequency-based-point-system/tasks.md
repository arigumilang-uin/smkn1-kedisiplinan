# Tasks Document: Frequency-Based Point System

## Overview

Dokumen ini berisi breakdown implementasi Frequency-Based Point System menjadi tasks yang actionable dan terukur. Setiap task reference ke requirements dan design document.

---

## Phase 1: Database & Models (Foundation)

### Task 1.1: Create Migration - `pelanggaran_frequency_rules` Table
**Reference:** Design Section 2.1  
**Requirements:** REQ-2, REQ-7

**Steps:**
1. Create migration file: `2025_12_06_100000_create_pelanggaran_frequency_rules_table.php`
2. Define schema dengan kolom: id, jenis_pelanggaran_id, frequency_min, frequency_max, poin, sanksi_description, trigger_surat, pembina_roles, display_order, timestamps
3. Add foreign key constraint ke `jenis_pelanggaran` dengan ON DELETE CASCADE
4. Add indexes: `idx_jenis_pelanggaran`, `idx_display_order`
5. Test migration: `php artisan migrate`

**Acceptance:**
- Migration runs successfully
- Table created dengan struktur yang benar
- Foreign key constraint berfungsi

---

### Task 1.2: Create Migration - Add `has_frequency_rules` to `jenis_pelanggaran`
**Reference:** Design Section 2.2  
**Requirements:** REQ-6, REQ-7

**Steps:**
1. Create migration file: `2025_12_06_100001_add_has_frequency_rules_to_jenis_pelanggaran.php`
2. Add column `has_frequency_rules` BOOLEAN DEFAULT FALSE after `poin`
3. Test migration: `php artisan migrate`

**Acceptance:**
- Migration runs successfully
- Column added dengan default value FALSE

---

### Task 1.3: Create Migration - Add Role `Waka Sarana`
**Reference:** Design Section 2.3  
**Requirements:** REQ-5, REQ-7

**Steps:**
1. Create migration file: `2025_12_06_100002_add_waka_sarana_role.php`
2. Insert new role: `INSERT INTO roles (nama_role) VALUES ('Waka Sarana')`
3. Test migration: `php artisan migrate`

**Acceptance:**
- Migration runs successfully
- Role `Waka Sarana` exists in database

---

### Task 1.4: Create Model - `PelanggaranFrequencyRule`
**Reference:** Design Section 3.1  
**Requirements:** REQ-2

**Steps:**
1. Create model: `php artisan make:model PelanggaranFrequencyRule`
2. Define fillable fields
3. Define casts: `trigger_surat` => boolean, `pembina_roles` => array
4. Add relation: `jenisPelanggaran()` belongsTo
5. Add helper method: `matchesFrequency(int $frequency): bool`
6. Add helper method: `getSuratType(): ?string`

**Acceptance:**
- Model created dengan relasi yang benar
- Helper methods berfungsi sesuai logic

---


### Task 1.5: Update Model - `JenisPelanggaran`
**Reference:** Design Section 3.2  
**Requirements:** REQ-2

**Steps:**
1. Open `app/Models/JenisPelanggaran.php`
2. Add `has_frequency_rules` to fillable array
3. Add relation: `frequencyRules()` hasMany with orderBy display_order
4. Add helper method: `usesFrequencyRules(): bool`

**Acceptance:**
- Relation berfungsi dengan benar
- Helper method return TRUE jika `has_frequency_rules = TRUE`

---

## Phase 2: Service Layer Refactoring

### Task 2.1: Create New Method - `evaluateFrequencyRules()`
**Reference:** Design Section 4.1.1  
**Requirements:** REQ-1

**Steps:**
1. Open `app/Services/PelanggaranRulesEngine.php`
2. Create private method `evaluateFrequencyRules(int $siswaId, JenisPelanggaran $pelanggaran): array`
3. Implement logic:
   - Hitung current frequency untuk siswa & pelanggaran
   - Load frequency rules dari pelanggaran
   - Find matched rule berdasarkan frequency
   - Check apakah threshold baru (compare dengan previous frequency)
   - Return array: poin_ditambahkan, surat_type, sanksi
4. Add unit test untuk method ini

**Acceptance:**
- Method return poin yang benar berdasarkan threshold
- Method return 0 jika threshold belum tercapai
- Method return 0 jika masih di range yang sama

---

### Task 2.2: Create New Method - `tentukanTipeSuratTertinggi()`
**Reference:** Design Section 4.1.3  
**Requirements:** REQ-3

**Steps:**
1. Open `app/Services/PelanggaranRulesEngine.php`
2. Create private method `tentukanTipeSuratTertinggi(array $suratTypes): ?string`
3. Implement logic:
   - Extract level dari array surat types
   - Find max level
   - Return surat type tertinggi
4. Add unit test untuk method ini

**Acceptance:**
- Method return surat type dengan level tertinggi
- Method TIDAK consider akumulasi poin (surat HANYA dari frequency rules)
- Method return null jika tidak ada surat types

---

### Task 2.3: Create New Method - `getPembinaanInternalRekomendasi()`
**Reference:** Design Section 4.1.4  
**Requirements:** REQ-4

**Steps:**
1. Open `app/Services/PelanggaranRulesEngine.php`
2. Create private method `getPembinaanInternalRekomendasi(int $totalPoin): array`
3. Implement logic berdasarkan range:
   - 0-50: Wali Kelas (konseling ringan)
   - 55-100: Wali Kelas + Kaprodi (monitoring ketat)
   - 105-300: Wali Kelas + Kaprodi + Waka (pembinaan intensif)
   - 305-500: Wali Kelas + Kaprodi + Waka + Kepsek (pembinaan kritis)
   - >500: Kepala Sekolah (dikembalikan ke orang tua)
4. Return array dengan pembina_roles dan keterangan
5. Add unit test untuk method ini

**Acceptance:**
- Method return rekomendasi pembina yang benar berdasarkan range poin
- Method TIDAK return surat type (hanya rekomendasi konseling)
- Method return array dengan struktur yang benar

---

### Task 2.4: Refactor Method - `processBatch()`
**Reference:** Design Section 4.1.2  
**Requirements:** REQ-1, REQ-6

**Steps:**
1. Open `app/Services/PelanggaranRulesEngine.php`
2. Update method `processBatch()`:
   - Eager load `frequencyRules` untuk pelanggaran
   - Loop setiap pelanggaran
   - Check `usesFrequencyRules()` untuk determine logic
   - Call `evaluateFrequencyRules()` jika TRUE
   - Use immediate accumulation jika FALSE (backward compatibility)
   - Collect poin baru dan surat types
   - Call `tentukanTipeSuratTertinggi()` untuk final surat type
   - Call `buatAtauUpdateTindakLanjut()` jika ada surat
3. Add integration test untuk method ini

**Acceptance:**
- Method support frequency-based evaluation
- Method support backward compatibility untuk pelanggaran tanpa frequency rules
- Method correctly determine surat type

---

### Task 2.5: Update Method - `reconcileForSiswa()`
**Reference:** Design Section 4.1  
**Requirements:** REQ-1

**Steps:**
1. Open `app/Services/PelanggaranRulesEngine.php`
2. Update method `reconcileForSiswa()`:
   - Eager load `frequencyRules` untuk pelanggaran
   - Use frequency-based evaluation untuk pelanggaran dengan frequency rules
   - Maintain backward compatibility
3. Add integration test untuk method ini

**Acceptance:**
- Method correctly recalculate poin dengan frequency rules
- Method maintain backward compatibility

---

## Phase 3: Role Waka Sarana

### Task 3.1: Create Dashboard Controller - `WakaSaranaDashboardController`
**Reference:** Design Section 5.1  
**Requirements:** REQ-5

**Steps:**
1. Create controller: `php artisan make:controller Dashboard/WakaSaranaDashboardController`
2. Implement method `index()`:
   - Query jenis pelanggaran fasilitas
   - Hitung statistik: total pelanggaran fasilitas, bulan ini
   - Query riwayat terbaru (10 records)
   - Query riwayat yang dicatat oleh Waka Sarana sendiri (5 records)
   - Return view dengan data
3. Create view: `resources/views/dashboards/waka_sarana.blade.php`

**Acceptance:**
- Dashboard menampilkan statistik pelanggaran fasilitas
- Dashboard menampilkan riwayat terbaru
- Dashboard menampilkan riwayat yang dicatat sendiri

---

### Task 3.2: Add Routes for Waka Sarana
**Reference:** Design Section 5.2  
**Requirements:** REQ-5

**Steps:**
1. Open `routes/web.php`
2. Add route: `GET /dashboard/waka-sarana` dengan middleware `role:Waka Sarana`
3. Update sidebar navigation untuk include link ke dashboard Waka Sarana

**Acceptance:**
- Route accessible untuk role Waka Sarana
- Sidebar menampilkan link untuk Waka Sarana

---

### Task 3.3: Update Access Control for Waka Sarana
**Reference:** Design Section 5.4  
**Requirements:** REQ-5

**Steps:**
1. Update route `/pelanggaran/catat` untuk include `Waka Sarana` di middleware
2. Update route `/riwayat/saya` untuk include `Waka Sarana` di middleware
3. Update `User` model untuk add helper method `isWakaSarana(): bool`

**Acceptance:**
- Waka Sarana bisa catat pelanggaran
- Waka Sarana bisa edit/hapus riwayat yang dia catat sendiri
- Waka Sarana tidak bisa edit/hapus riwayat orang lain

---

## Phase 4: Data Migration & Seeding

### Task 4.1: Create Seeder - `FrequencyRulesSeeder`
**Reference:** Design Section 7.2  
**Requirements:** REQ-7

**Steps:**
1. Create seeder: `php artisan make:seeder FrequencyRulesSeeder`
2. Implement seeding untuk semua pelanggaran dari tata tertib:
   - RINGAN: Atribut (10x), Terlambat apel (10x), Terlambat tidak apel (4x), Sholat (4x)
   - SEDANG: Alfa (1-3, 4+), Cabut (1, 2+), Kegiatan (1+), HP (3+), Fasilitas (1+)
   - BERAT: Semua pelanggaran berat (frekuensi = 1, immediate)
3. Update flag `has_frequency_rules` untuk pelanggaran yang di-seed
4. Test seeder: `php artisan db:seed --class=FrequencyRulesSeeder`

**Acceptance:**
- Semua frequency rules ter-seed dengan benar
- Flag `has_frequency_rules` updated untuk pelanggaran yang sesuai
- Data sesuai dengan tata tertib yang user berikan

---

### Task 4.2: Update DatabaseSeeder
**Reference:** Design Section 7  
**Requirements:** REQ-7

**Steps:**
1. Open `database/seeders/DatabaseSeeder.php`
2. Add `FrequencyRulesSeeder::class` ke call stack
3. Ensure seeder run setelah `JenisPelanggaranSeeder`

**Acceptance:**
- Seeder run otomatis saat `php artisan db:seed`
- Order seeder benar (JenisPelanggaran → FrequencyRules)

---


## Phase 5: UI - Frequency Rules Management (Operator)

### Task 5.1: Create Controller - `FrequencyRulesController`
**Reference:** Design Section 6.1  
**Requirements:** REQ-8

**Steps:**
1. Create controller: `php artisan make:controller FrequencyRulesController`
2. Implement methods:
   - `index()`: List semua jenis pelanggaran dengan toggle frequency rules
   - `show($jenisPelanggaranId)`: Show detail frequency rules untuk satu pelanggaran
   - `store(Request $request, $jenisPelanggaranId)`: Create new frequency rule
   - `update(Request $request, $ruleId)`: Update existing frequency rule
   - `destroy($ruleId)`: Delete frequency rule
3. Add validation:
   - `frequency_min` required, integer, >= 1
   - `frequency_max` nullable, integer, >= frequency_min
   - `poin` required, integer, >= 0
   - `sanksi_description` required, string
   - `trigger_surat` required, boolean
   - `pembina_roles` required, array
   - Validate threshold tidak overlap dengan rules existing

**Acceptance:**
- Controller methods berfungsi dengan benar
- Validation mencegah data inconsistency
- Threshold overlap detection berfungsi

---

### Task 5.2: Create Routes for Frequency Rules Management
**Reference:** Design Section 6.1  
**Requirements:** REQ-8

**Steps:**
1. Open `routes/web.php`
2. Add routes dengan middleware `role:Operator Sekolah`:
   - `GET /frequency-rules` → index
   - `GET /frequency-rules/{jenisPelanggaran}` → show
   - `POST /frequency-rules/{jenisPelanggaran}` → store
   - `PUT /frequency-rules/{rule}` → update
   - `DELETE /frequency-rules/{rule}` → destroy

**Acceptance:**
- Routes accessible untuk Operator Sekolah
- Routes protected dari role lain

---

### Task 5.3: Create View - Frequency Rules Index
**Reference:** Design Section 6.1  
**Requirements:** REQ-8

**Steps:**
1. Create view: `resources/views/frequency-rules/index.blade.php`
2. Implement UI:
   - Table list semua jenis pelanggaran
   - Toggle switch untuk enable/disable frequency rules
   - Button "Kelola Rules" untuk pelanggaran dengan frequency rules
   - Filter by kategori pelanggaran
3. Add JavaScript untuk toggle switch (AJAX update)

**Acceptance:**
- View menampilkan semua jenis pelanggaran
- Toggle switch berfungsi dengan AJAX
- Filter by kategori berfungsi

---

### Task 5.4: Create View - Frequency Rules Detail
**Reference:** Design Section 6.1  
**Requirements:** REQ-8

**Steps:**
1. Create view: `resources/views/frequency-rules/show.blade.php`
2. Implement UI:
   - Header dengan nama pelanggaran dan kategori
   - Table list frequency rules dengan kolom: Range Frekuensi, Poin, Sanksi, Trigger Surat, Pembina, Actions
   - Button "Tambah Rule"
   - Modal form untuk tambah/edit rule
   - Confirmation modal untuk delete rule
3. Add JavaScript untuk modal interactions

**Acceptance:**
- View menampilkan frequency rules untuk pelanggaran
- Modal form berfungsi untuk tambah/edit
- Delete confirmation berfungsi

---

### Task 5.5: Add Sidebar Link for Frequency Rules
**Reference:** Design Section 6.1  
**Requirements:** REQ-8

**Steps:**
1. Open `resources/views/layouts/app.blade.php`
2. Add sidebar link "Kelola Frequency Rules" untuk Operator Sekolah
3. Add icon: `<i class="bi bi-sliders"></i>`

**Acceptance:**
- Sidebar link visible untuk Operator Sekolah
- Link navigate ke frequency rules index

---

## Phase 6: UI - Real-time Frequency Display (Guru)

### Task 6.1: Create API Endpoint - Preview Frequency
**Reference:** Design Section 6.2  
**Requirements:** REQ-9

**Steps:**
1. Open `app/Http/Controllers/PelanggaranController.php`
2. Add method `previewFrequency(Request $request)`:
   - Validate: siswa_id, jenis_pelanggaran_id
   - Query current frequency untuk siswa & pelanggaran
   - Load frequency rules untuk pelanggaran
   - Find next threshold
   - Calculate poin yang akan ditambahkan
   - Return JSON: { frequency, next_threshold, poin_akan_ditambahkan, sanksi, warning }
3. Add route: `GET /api/pelanggaran/preview-frequency`

**Acceptance:**
- Endpoint return data yang benar
- Endpoint handle edge cases (no rules, no next threshold)

---

### Task 6.2: Update View - Pencatatan Pelanggaran Form
**Reference:** Design Section 6.2  
**Requirements:** REQ-9

**Steps:**
1. Open `resources/views/pelanggaran/create.blade.php`
2. Add section untuk preview frequency:
   - Display current frequency
   - Display warning jika threshold akan tercapai
   - Display preview poin yang akan ditambahkan
   - Display preview sanksi
3. Add JavaScript:
   - AJAX call ke `/api/pelanggaran/preview-frequency` saat siswa & pelanggaran dipilih
   - Update preview section dengan response data
   - Show warning badge jika threshold akan tercapai

**Acceptance:**
- Preview section menampilkan data yang benar
- AJAX call triggered saat siswa & pelanggaran dipilih
- Warning badge visible jika threshold akan tercapai

---

## Phase 7: Testing & Quality Assurance

### Task 7.1: Unit Tests - Models
**Reference:** Design Section 8.1  
**Requirements:** ALL

**Steps:**
1. Create test: `tests/Unit/PelanggaranFrequencyRuleTest.php`
2. Test methods:
   - `matchesFrequency()` dengan berbagai scenarios
   - `getSuratType()` dengan berbagai kombinasi pembina
3. Create test: `tests/Unit/JenisPelanggaranTest.php`
4. Test method: `usesFrequencyRules()`

**Acceptance:**
- All unit tests pass
- Code coverage >= 80%

---

### Task 7.2: Unit Tests - Service Layer
**Reference:** Design Section 8.1  
**Requirements:** REQ-1, REQ-3, REQ-4

**Steps:**
1. Create test: `tests/Unit/PelanggaranRulesEngineTest.php`
2. Test methods:
   - `evaluateFrequencyRules()` dengan berbagai scenarios
   - `tentukanTipeSuratTertinggi()` dengan berbagai combinations
   - `getPembinaanInternalRekomendasi()` dengan berbagai ranges
3. Mock dependencies: RiwayatPelanggaran, JenisPelanggaran

**Acceptance:**
- All unit tests pass
- Edge cases covered
- Test memastikan akumulasi poin TIDAK trigger surat

---

### Task 7.3: Integration Tests - Pencatatan Pelanggaran
**Reference:** Design Section 8.2  
**Requirements:** REQ-1, REQ-6

**Steps:**
1. Create test: `tests/Feature/PelanggaranFrequencyTest.php`
2. Test scenarios:
   - Catat pelanggaran dengan frequency rules → poin sesuai threshold
   - Catat pelanggaran tanpa frequency rules → poin langsung (backward compatibility)
   - Multiple threshold untuk satu pelanggaran
   - Threshold tercapai → surat triggered
   - Threshold belum tercapai → tidak ada surat
3. Use database transactions untuk isolasi

**Acceptance:**
- All integration tests pass
- Scenarios cover happy path dan edge cases

---

### Task 7.4: Integration Tests - Waka Sarana
**Reference:** Design Section 8.2  
**Requirements:** REQ-5

**Steps:**
1. Create test: `tests/Feature/WakaSaranaTest.php`
2. Test scenarios:
   - Waka Sarana bisa access dashboard
   - Waka Sarana bisa catat pelanggaran
   - Waka Sarana bisa edit/hapus riwayat sendiri
   - Waka Sarana tidak bisa edit/hapus riwayat orang lain
3. Use database transactions untuk isolasi

**Acceptance:**
- All integration tests pass
- Access control berfungsi dengan benar

---

### Task 7.5: Manual Testing - End-to-End Scenarios
**Reference:** Design Section 8.3  
**Requirements:** ALL

**Steps:**
1. Test Scenario 1: Alfa 4x
   - Login sebagai Guru
   - Catat Alfa untuk siswa (1x, 2x, 3x, 4x)
   - Verify poin: 0, 0, 25, 50
   - Verify surat: tidak ada, tidak ada, tidak ada, Surat 1
2. Test Scenario 2: Atribut 10x
   - Login sebagai Wali Kelas
   - Catat Atribut untuk siswa (1-9x, 10x)
   - Verify poin: 0, 5
   - Verify surat: tidak ada, Surat 1
3. Test Scenario 3: Merokok 1x (Pelanggaran Berat)
   - Login sebagai Waka Kesiswaan
   - Catat Merokok untuk siswa (1x)
   - Verify poin: 100
   - Verify surat: Surat 2
4. Test Scenario 4: Akumulasi Poin → Eskalasi Surat
   - Setup siswa dengan 60 poin
   - Verify surat: Surat 2
   - Catat pelanggaran hingga 150 poin
   - Verify surat: Surat 3 (eskalasi)

**Acceptance:**
- All scenarios pass
- Poin dan surat sesuai dengan expected results

---

## Phase 8: Documentation & Deployment

### Task 8.1: Update User Documentation
**Reference:** Design Section 12  
**Requirements:** ALL

**Steps:**
1. Create file: `FREQUENCY_BASED_POINT_SYSTEM.md`
2. Document:
   - Overview sistem baru
   - Perbedaan dengan sistem lama
   - Cara kerja frequency rules
   - Cara manage frequency rules (Operator)
   - Cara melihat preview frequency (Guru)
   - FAQ dan troubleshooting
3. Add screenshots untuk UI baru

**Acceptance:**
- Documentation lengkap dan mudah dipahami
- Screenshots jelas dan informatif

---

### Task 8.2: Update Technical Documentation
**Reference:** Design Section 1-11  
**Requirements:** ALL

**Steps:**
1. Update `README.md` dengan informasi sistem baru
2. Update API documentation untuk endpoint baru
3. Update database schema documentation
4. Add comments di code untuk complex logic

**Acceptance:**
- Technical documentation up-to-date
- Code comments jelas dan helpful

---

### Task 8.3: Database Backup & Migration Plan
**Reference:** Design Section 11  
**Requirements:** REQ-7

**Steps:**
1. Create backup script: `scripts/backup-before-frequency-migration.sh`
2. Document migration steps:
   - Backup database
   - Run migrations
   - Run seeders
   - Verify data integrity
   - Rollback plan jika ada masalah
3. Test migration di staging environment

**Acceptance:**
- Backup script berfungsi
- Migration steps documented
- Rollback plan tested

---

### Task 8.4: Deployment to Production
**Reference:** Design Section 11  
**Requirements:** ALL

**Steps:**
1. Schedule maintenance window
2. Notify users tentang downtime
3. Execute deployment:
   - Backup database
   - Pull latest code
   - Run migrations
   - Run seeders
   - Clear cache
   - Restart services
4. Verify deployment:
   - Test critical paths
   - Monitor error logs
   - Check performance metrics
5. Post-deployment monitoring (24 hours)

**Acceptance:**
- Deployment successful tanpa errors
- All critical paths berfungsi
- Performance metrics normal

---

## Phase 9: Post-Deployment

### Task 9.1: Monitor & Bug Fixes
**Reference:** Design Section 12  
**Requirements:** ALL

**Steps:**
1. Monitor error logs untuk 1 minggu
2. Collect user feedback
3. Fix bugs yang ditemukan
4. Optimize performance jika diperlukan

**Acceptance:**
- No critical bugs
- User feedback positive
- Performance metrics meet targets

---

### Task 9.2: Training & Support
**Reference:** Design Section 12  
**Requirements:** ALL

**Steps:**
1. Conduct training session untuk Operator (frequency rules management)
2. Conduct training session untuk Guru (preview frequency)
3. Conduct training session untuk Waka Sarana (dashboard baru)
4. Provide support untuk user questions

**Acceptance:**
- All users trained
- Support tickets resolved

---

## Summary

Total tasks: **40 tasks** across 9 phases

**Estimated Timeline:**
- Phase 1: 2 days (Database & Models)
- Phase 2: 3 days (Service Layer)
- Phase 3: 2 days (Waka Sarana)
- Phase 4: 1 day (Data Migration)
- Phase 5: 3 days (UI - Operator)
- Phase 6: 2 days (UI - Guru)
- Phase 7: 3 days (Testing)
- Phase 8: 2 days (Documentation & Deployment)
- Phase 9: 1 week (Post-Deployment)

**Total: ~3 weeks** (excluding post-deployment monitoring)

**Critical Path:**
Phase 1 → Phase 2 → Phase 4 → Phase 7 → Phase 8

**Parallel Work:**
- Phase 3 (Waka Sarana) dapat dikerjakan parallel dengan Phase 2
- Phase 5 & 6 (UI) dapat dikerjakan parallel setelah Phase 2 selesai

---

## Next Steps

1. Review tasks document dengan user
2. Confirm timeline dan resource allocation
3. Start implementation dari Phase 1
4. Daily standup untuk track progress
5. Weekly review untuk adjust timeline jika diperlukan
