# Dokumentasi: Sistem Surat Panggilan Fleksibel

## Overview

Sistem surat panggilan orang tua yang **benar-benar fleksibel** berdasarkan pembina yang dipilih di frequency rules. Tidak ada hierarchy yang hardcoded, semua ditentukan oleh operator saat membuat frequency rules.

---

## Prinsip Fleksibilitas

### ❌ BUKAN Sistem Statis:
```
Surat 1 = Wali Kelas + Mengetahui Kepsek
Surat 2 = Wali Kelas + Kaprodi + Mengetahui Kepsek
Surat 3 = Wali Kelas + Kaprodi + Waka + Mengetahui Kepsek
```

### ✅ Sistem Fleksibel:
```
Surat = Pembina yang dipilih di frequency rules (APAPUN kombinasinya)
```

---

## Contoh Skenario Penggunaan

### Skenario 1: Pelanggaran Ringan (Atribut)
**Frequency Rule:**
- Frekuensi 1-9x: Pembina "Semua Guru & Staff" (tidak trigger surat)
- Frekuensi 10+x: Pembina "Wali Kelas", Trigger Surat

**Hasil Surat:**
```
Layout: 1 kolom
├── Wali Kelas (kiri)
```

**Alasan:** Pelanggaran ringan cukup ditangani Wali Kelas, tidak perlu melibatkan Kepsek.

---

### Skenario 2: Pelanggaran Sedang (Alfa 4x)
**Frequency Rule:**
- Frekuensi 1-3x: Pembina "Wali Kelas" (tidak trigger surat, hanya pembinaan)
- Frekuensi 4+x: Pembina "Wali Kelas", Trigger Surat

**Hasil Surat:**
```
Layout: 1 kolom
├── Wali Kelas (kiri)
```

**Alasan:** Alfa berulang perlu panggilan orang tua, tapi masih bisa ditangani Wali Kelas.

---

### Skenario 3: Pelanggaran Sedang Berulang (Cabut 2x)
**Frequency Rule:**
- Frekuensi 1x: Pembina "Wali Kelas" (tidak trigger surat)
- Frekuensi 2+x: Pembina "Wali Kelas" + "Kaprodi", Trigger Surat

**Hasil Surat:**
```
Layout: 2 kolom (50% width each)
├── Wali Kelas (kiri) | Kaprodi (kanan)
```

**Alasan:** Cabut berulang perlu eskalasi ke Kaprodi untuk monitoring lebih ketat.

---

### Skenario 4: Pelanggaran Berat (Merokok)
**Frequency Rule:**
- Frekuensi 1x: Pembina "Wali Kelas" + "Kaprodi" + "Waka Kesiswaan", Trigger Surat

**Hasil Surat:**
```
Layout: 3 kolom (33% width each)
├── Wali Kelas (kiri) | Kaprodi (tengah) | Waka Kesiswaan (kanan)
```

**Alasan:** Pelanggaran berat perlu penanganan serius dari 3 pihak.

---

### Skenario 5: Pelanggaran Sangat Berat (Narkoba)
**Frequency Rule:**
- Frekuensi 1x: Pembina "Wali Kelas" + "Kaprodi" + "Waka Kesiswaan" + "Kepala Sekolah", Trigger Surat

**Hasil Surat:**
```
Layout: 4 kolom (25% width each)
├── Wali Kelas | Kaprodi | Waka Kesiswaan | Kepala Sekolah
```

**Alasan:** Pelanggaran sangat berat perlu melibatkan Kepala Sekolah langsung.

---

### Skenario 6: Kasus Khusus (Kombinasi Custom)
**Frequency Rule:**
- Frekuensi 1x: Pembina "Wali Kelas" + "Waka Kesiswaan", Trigger Surat

**Hasil Surat:**
```
Layout: 2 kolom (50% width each)
├── Wali Kelas (kiri) | Waka Kesiswaan (kanan)
```

**Alasan:** Kasus tertentu mungkin perlu Waka Kesiswaan tapi tidak perlu Kaprodi.

---

### Skenario 7: Pelanggaran Fasilitas
**Frequency Rule:**
- Frekuensi 1x: Pembina "Waka Sarana" + "Kepala Sekolah", Trigger Surat

**Hasil Surat:**
```
Layout: 2 kolom (50% width each)
├── Waka Sarana (kiri) | Kepala Sekolah (kanan)
```

**Alasan:** Pelanggaran fasilitas ditangani Waka Sarana dengan persetujuan Kepsek.

---

### Skenario 8: Banyak Pembina (5+ pembina)
**Frequency Rule:**
- Frekuensi 1x: Pembina "Wali Kelas" + "Kaprodi" + "Waka Kesiswaan" + "Waka Sarana" + "Kepala Sekolah", Trigger Surat

**Hasil Surat:**
```
Layout: 2 baris, 2 kolom per baris
Baris 1: Wali Kelas (kiri) | Kaprodi (kanan)
Baris 2: Waka Kesiswaan (kiri) | Waka Sarana (kanan)
Baris 3: Kepala Sekolah (tengah)
```

**Alasan:** Untuk kasus sangat kompleks yang perlu semua pihak terlibat.

---

## Keuntungan Sistem Fleksibel

### 1. **Tidak Ada Hierarchy Hardcoded**
- Operator bebas menentukan pembina sesuai kebutuhan
- Tidak terbatas pada pola Surat 1, 2, 3, 4
- Bisa kombinasi apapun

### 2. **Efisiensi Penanganan**
- Pelanggaran ringan tidak perlu melibatkan Kepsek
- Kepsek hanya terlibat untuk kasus yang benar-benar perlu
- Setiap pembina fokus pada kasus yang sesuai dengan tanggung jawabnya

### 3. **Adaptif untuk Masa Depan**
- Jika ada role baru (misal: Waka Kurikulum), tinggal tambahkan di frequency rules
- Jika ada perubahan kebijakan, tinggal update frequency rules
- Tidak perlu ubah kode program

### 4. **Konsisten dengan Frequency Rules**
- Pembina di surat = Pembina di frequency rules
- Tidak ada pembina tambahan yang muncul tiba-tiba
- Transparan dan mudah dipahami

---

## Implementasi Teknis

### Database Schema

**users table:**
```sql
- id
- nama
- nip (NEW: untuk tanda tangan surat)
- role_id
- ...
```

**surat_panggilan table:**
```sql
- id
- tindak_lanjut_id
- nomor_surat
- tipe_surat
- pembina_data (JSON: array of {jabatan, nama, nip})
- tanggal_surat
- tanggal_pertemuan
- waktu_pertemuan
- keperluan
- file_path_pdf
```

### Service Logic

**SuratPanggilanService::getPembinaData()**
```php
// Ambil pembina berdasarkan role yang dipilih di frequency rules
// - Wali Kelas: dari kelas.wali_kelas_user_id
// - Kaprodi: dari jurusan.kaprodi_user_id
// - Waka Kesiswaan/Sarana: query by role
// - Kepala Sekolah: query by role

// TIDAK ADA logic "always add Kepsek"
// MURNI berdasarkan input pembinaRoles
```

### Template Logic

**template.blade.php:**
```blade
// Layout tanda tangan menyesuaikan jumlah pembina:
// - 1-2 pembina: 2 kolom (50% width)
// - 3 pembina: 3 kolom (33% width)
// - 4 pembina: 4 kolom (25% width)
// - 5+ pembina: 2 baris, 2-3 kolom per baris

// TIDAK ADA section "Mengetahui Kepala Sekolah"
// Semua pembina ditampilkan dengan format yang sama
```

---

## Cara Penggunaan

### 1. Setup NIP untuk Semua Pembina
```sql
UPDATE users SET nip = '19730322 200012 2 002' WHERE nama = 'SALMIAH, S.Pd.MM';
UPDATE users SET nip = '...' WHERE role_id = (SELECT id FROM roles WHERE nama_role = 'Wali Kelas');
-- dst untuk semua pembina
```

### 2. Buat Frequency Rule dengan Pembina
```php
// Di form frequency rules, pilih pembina yang terlibat:
pembina_roles = ['Wali Kelas', 'Kaprodi']
trigger_surat = true
```

### 3. Generate Surat Otomatis
```php
// Saat threshold tercapai, PelanggaranRulesEngine akan:
$service = new SuratPanggilanService();
$surat = $service->generateSurat($tindakLanjut, $pembinaRoles, $keperluan);
```

### 4. Preview/Download Surat
```php
// Controller untuk preview surat:
return view('surat-panggilan.template', [
    'surat' => $surat,
    'siswa' => $surat->tindakLanjut->siswa,
]);

// Generate PDF:
$pdf = PDF::loadView('surat-panggilan.template', compact('surat', 'siswa'));
return $pdf->download('surat-panggilan-' . $siswa->nama_siswa . '.pdf');
```

---

## Testing Scenarios

### Test 1: Surat dengan 1 Pembina
```php
$pembinaRoles = ['Wali Kelas'];
$surat = $service->generateSurat($tindakLanjut, $pembinaRoles);
// Expected: Layout 1 kolom, hanya Wali Kelas
```

### Test 2: Surat dengan 2 Pembina
```php
$pembinaRoles = ['Wali Kelas', 'Kaprodi'];
$surat = $service->generateSurat($tindakLanjut, $pembinaRoles);
// Expected: Layout 2 kolom, Wali Kelas + Kaprodi
```

### Test 3: Surat dengan 3 Pembina
```php
$pembinaRoles = ['Wali Kelas', 'Kaprodi', 'Waka Kesiswaan'];
$surat = $service->generateSurat($tindakLanjut, $pembinaRoles);
// Expected: Layout 3 kolom
```

### Test 4: Surat dengan 4 Pembina (Include Kepsek)
```php
$pembinaRoles = ['Wali Kelas', 'Kaprodi', 'Waka Kesiswaan', 'Kepala Sekolah'];
$surat = $service->generateSurat($tindakLanjut, $pembinaRoles);
// Expected: Layout 4 kolom, Kepsek di kolom ke-4 (BUKAN "Mengetahui")
```

### Test 5: Kombinasi Custom
```php
$pembinaRoles = ['Waka Sarana', 'Kepala Sekolah'];
$surat = $service->generateSurat($tindakLanjut, $pembinaRoles);
// Expected: Layout 2 kolom, Waka Sarana + Kepsek
```

---

## FAQ

### Q: Apakah Kepala Sekolah selalu harus ada di surat?
**A:** TIDAK. Kepala Sekolah hanya ada jika dipilih di frequency rules. Untuk pelanggaran ringan/sedang, tidak perlu melibatkan Kepsek.

### Q: Bagaimana jika ada 5+ pembina?
**A:** Template otomatis split menjadi 2 baris dengan 2-3 kolom per baris.

### Q: Apakah bisa kombinasi pembina yang tidak umum (misal: hanya Kaprodi + Waka)?
**A:** BISA. Sistem 100% fleksibel, tidak ada batasan kombinasi.

### Q: Bagaimana jika pembina tidak punya NIP?
**A:** Template akan menampilkan garis titik-titik `(...............)` sebagai placeholder untuk tanda tangan manual.

### Q: Apakah format surat bisa disesuaikan per sekolah?
**A:** YA. Template blade bisa dimodifikasi sesuai kebutuhan (logo, kop surat, format, dll).

---

## Kesimpulan

Sistem surat panggilan ini **benar-benar fleksibel** dan tidak terikat pada hierarchy statis. Operator memiliki kontrol penuh untuk menentukan pembina yang terlibat sesuai dengan tingkat keparahan pelanggaran dan kebijakan sekolah.

**Prinsip Utama:**
1. ✅ Fleksibel: Kombinasi pembina apapun bisa
2. ✅ Efisien: Tidak semua kasus perlu Kepsek
3. ✅ Transparan: Pembina di surat = Pembina di frequency rules
4. ✅ Adaptif: Mudah disesuaikan untuk kebutuhan masa depan

---

## Implementation Status

### ✅ Completed (2025-12-07)

**1. Database Schema:**
- ✅ Added `nip` column to `users` table
- ✅ Added `pembina_data` (JSON), `tanggal_pertemuan`, `waktu_pertemuan`, `keperluan` to `surat_panggilan` table
- ✅ Updated models: `User`, `SuratPanggilan`

**2. Service Layer (Clean Architecture):**
- ✅ Created `SuratPanggilanService.php` with focused responsibilities:
  - `buildPembinaData()` - Query pembina from database based on roles
  - `generateNomorSurat()` - Generate surat number with proper format
  - `setDefaultMeetingSchedule()` - Set default meeting date/time (3 days, 09:00)
- ✅ Updated `PelanggaranRulesEngine.php` to use service:
  - Modified `buatAtauUpdateTindakLanjut()` to build pembina_data
  - Modified `eskalasiBilaPerluan()` to update pembina_data on escalation
  - Modified `reconcileForSiswa()` to rebuild pembina_data on reconciliation
  - Modified `evaluateFrequencyRules()` to return pembina_roles

**3. Template (Flexible Layout):**
- ✅ Replaced `resources/views/surat/template_umum.blade.php` with flexible layout:
  - Dynamic signature layout based on pembina count (1-2: 2 cols, 3: 3 cols, 4+: 4 cols)
  - No hardcoded "Mengetahui Kepala Sekolah" section
  - All pembina displayed with equal importance
  - Proper formatting with jabatan, nama, NIP
  - Fallback for backward compatibility (if no pembina_data)

**4. Controller:**
- ✅ No changes needed to `TindakLanjutController::cetakSurat()` - already works with new template

---

## How It Works

### Flow Diagram:
```
Pelanggaran Recorded
    ↓
PelanggaranRulesEngine::processBatch()
    ↓
evaluateFrequencyRules() → Returns: poin, surat_type, pembina_roles
    ↓
buatAtauUpdateTindakLanjut()
    ↓
SuratPanggilanService::buildPembinaData(pembinaRoles, siswa)
    ├── Query Wali Kelas from kelas.wali_kelas_user_id
    ├── Query Kaprodi from jurusan.kaprodi_user_id
    ├── Query Waka Kesiswaan by role
    ├── Query Waka Sarana by role
    └── Query Kepala Sekolah by role
    ↓
Create SuratPanggilan with pembina_data (JSON)
    ↓
TindakLanjutController::cetakSurat()
    ↓
template_umum.blade.php renders flexible layout
    ↓
PDF Generated with correct pembina signatures
```

---

**Last Updated:** 2025-12-07  
**Status:** ✅ Fully Implemented & Ready for Testing
