# Testing Guide: Pembinaan Internal Rules Management

## Overview
Dokumen ini berisi panduan testing untuk fitur Pembinaan Internal Rules Management yang baru diimplementasi.

---

## Prerequisites

1. **Migration & Seeder sudah dijalankan:**
   ```bash
   php artisan migrate
   php artisan db:seed --class=PembinaanInternalRulesSeeder
   ```

2. **Login sebagai Operator Sekolah** (hanya Operator yang bisa akses fitur ini)

---

## Test Scenarios

### 1. Akses Halaman Pembinaan Internal Rules

**Steps:**
1. Login sebagai Operator Sekolah
2. Klik menu "Pembinaan Internal" di sidebar
3. URL: `/pembinaan-internal-rules`

**Expected Result:**
- ✅ Halaman terbuka tanpa error
- ✅ Menampilkan 5 default rules yang sudah di-seed:
  - 0-50 poin: Wali Kelas
  - 55-100 poin: Wali Kelas + Kaprodi
  - 105-300 poin: Wali Kelas + Kaprodi + Waka
  - 305-500 poin: Semua pembina
  - 501+ poin: Kepala Sekolah
- ✅ Terdapat button "Tambah Aturan Baru"
- ✅ Setiap rule memiliki button Edit dan Hapus
- ✅ Info boxes menjelaskan perbedaan Frequency Rules vs Pembinaan Internal

---

### 2. Tambah Aturan Baru (Valid)

**Steps:**
1. Klik button "Tambah Aturan Baru"
2. Modal terbuka
3. Isi form:
   - Poin Minimum: `51`
   - Poin Maximum: `54`
   - Pembina: Centang "Wali Kelas" dan "Kaprodi"
   - Keterangan: `Pembinaan transisi, monitoring awal`
   - Display Order: Kosongkan (auto)
4. Klik "Simpan"

**Expected Result:**
- ✅ Modal tertutup
- ✅ Alert success muncul: "Aturan pembinaan internal berhasil ditambahkan"
- ✅ Rule baru muncul di tabel dengan urutan yang benar
- ✅ Data tersimpan di database

---

### 3. Tambah Aturan dengan Range Overlap (Invalid)

**Steps:**
1. Klik button "Tambah Aturan Baru"
2. Isi form:
   - Poin Minimum: `40`
   - Poin Maximum: `60`
   - Pembina: Centang "Wali Kelas"
   - Keterangan: `Test overlap`
3. Klik "Simpan"

**Expected Result:**
- ❌ Validation error muncul
- ❌ Error message: "Range overlap dengan aturan existing (range: 0-50 poin)" atau "(range: 55-100 poin)"
- ❌ Form tidak submit
- ❌ Data tidak tersimpan

---

### 4. Edit Aturan Existing

**Steps:**
1. Klik button Edit (icon pensil) pada rule "0-50 poin"
2. Modal edit terbuka dengan data existing
3. Ubah keterangan menjadi: `Pembinaan ringan, konseling individual`
4. Klik "Update"

**Expected Result:**
- ✅ Modal tertutup
- ✅ Alert success: "Aturan pembinaan internal berhasil diupdate"
- ✅ Keterangan di tabel berubah sesuai edit
- ✅ Data terupdate di database

---

### 5. Edit dengan Range Overlap (Invalid)

**Steps:**
1. Klik button Edit pada rule "55-100 poin"
2. Ubah Poin Minimum menjadi: `45` (overlap dengan rule 0-50)
3. Klik "Update"

**Expected Result:**
- ❌ Validation error muncul
- ❌ Error message: "Range overlap dengan aturan existing (range: 0-50 poin)"
- ❌ Form tidak submit
- ❌ Data tidak berubah

---

### 6. Hapus Aturan

**Steps:**
1. Klik button Hapus (icon trash) pada rule yang baru ditambahkan (51-54 poin)
2. Konfirmasi delete di browser alert
3. Klik OK

**Expected Result:**
- ✅ Alert success: "Aturan pembinaan internal berhasil dihapus"
- ✅ Rule hilang dari tabel
- ✅ Data terhapus dari database

---

### 7. Validasi Form - Poin Minimum Required

**Steps:**
1. Klik "Tambah Aturan Baru"
2. Kosongkan Poin Minimum
3. Isi field lainnya
4. Klik "Simpan"

**Expected Result:**
- ❌ Validation error: "The poin min field is required"
- ❌ Form tidak submit

---

### 8. Validasi Form - Poin Max < Poin Min

**Steps:**
1. Klik "Tambah Aturan Baru"
2. Isi Poin Minimum: `100`
3. Isi Poin Maximum: `50`
4. Isi field lainnya
5. Klik "Simpan"

**Expected Result:**
- ❌ Validation error: "The poin max field must be greater than or equal to poin min"
- ❌ Form tidak submit

---

### 9. Validasi Form - Pembina Tidak Dipilih

**Steps:**
1. Klik "Tambah Aturan Baru"
2. Isi Poin Minimum dan Maximum
3. JANGAN centang pembina apapun
4. Isi keterangan
5. Klik "Simpan"

**Expected Result:**
- ❌ Validation error: "The pembina roles field is required"
- ❌ Form tidak submit

---

### 10. Open-Ended Range (Poin Max Kosong)

**Steps:**
1. Klik "Tambah Aturan Baru"
2. Isi Poin Minimum: `1000`
3. Kosongkan Poin Maximum (untuk open-ended)
4. Centang "Kepala Sekolah"
5. Keterangan: `Kasus ekstrem, evaluasi khusus`
6. Klik "Simpan"

**Expected Result:**
- ✅ Rule tersimpan dengan poin_max = NULL
- ✅ Display di tabel: "1000+ poin"
- ✅ Rule ini akan match untuk semua poin >= 1000

---

## Integration Testing

### Test 1: Rekomendasi Pembinaan untuk Siswa

**Setup:**
1. Buat siswa test dengan beberapa pelanggaran
2. Total poin akumulasi: 75 poin

**Test Code (Tinker):**
```php
php artisan tinker

$rulesEngine = new \App\Services\PelanggaranRulesEngine();
$siswaId = 1; // Ganti dengan ID siswa test

$totalPoin = $rulesEngine->hitungTotalPoinAkumulasi($siswaId);
echo "Total Poin: {$totalPoin}\n";

$rekomendasi = $rulesEngine->getPembinaanInternalRekomendasi($totalPoin);
print_r($rekomendasi);
```

**Expected Output:**
```
Total Poin: 75
Array
(
    [pembina_roles] => Array
        (
            [0] => Wali Kelas
            [1] => Kaprodi
        )
    [keterangan] => Pembinaan sedang, monitoring ketat
    [range_text] => 55-100 poin
)
```

---

### Test 2: Get Siswa yang Perlu Pembinaan

**Test Code (Tinker):**
```php
php artisan tinker

$rulesEngine = new \App\Services\PelanggaranRulesEngine();

// Get semua siswa yang perlu pembinaan (poin > 0)
$siswaList = $rulesEngine->getSiswaPerluPembinaan();

echo "Total siswa perlu pembinaan: " . $siswaList->count() . "\n";

foreach ($siswaList as $item) {
    echo sprintf(
        "- %s: %d poin (%s)\n",
        $item['siswa']->nama_lengkap,
        $item['total_poin'],
        $item['rekomendasi']['keterangan']
    );
}
```

**Expected Output:**
```
Total siswa perlu pembinaan: 5
- Ahmad Fauzi: 150 poin (Pembinaan intensif, evaluasi berkala)
- Siti Nurhaliza: 75 poin (Pembinaan sedang, monitoring ketat)
- Budi Santoso: 45 poin (Pembinaan ringan, konseling)
...
```

---

### Test 3: Filter Siswa by Poin Range

**Test Code (Tinker):**
```php
php artisan tinker

$rulesEngine = new \App\Services\PelanggaranRulesEngine();

// Get siswa dengan poin 55-100 (pembinaan sedang)
$siswaList = $rulesEngine->getSiswaPerluPembinaan(55, 100);

echo "Siswa dengan poin 55-100: " . $siswaList->count() . "\n";
```

---

## UI/UX Testing

### Checklist:
- [ ] Modal tambah/edit berfungsi dengan baik
- [ ] Form validation bekerja (client-side & server-side)
- [ ] Alert success muncul dan auto-dismiss setelah 5 detik
- [ ] Konfirmasi delete muncul sebelum hapus
- [ ] Tabel responsive di mobile
- [ ] Badge pembina roles tampil dengan baik
- [ ] Info boxes mudah dibaca dan informatif
- [ ] Sidebar link "Pembinaan Internal" active saat di halaman ini
- [ ] Tidak ada console error di browser

---

## Database Testing

### Verify Data Integrity:

```sql
-- Check all rules
SELECT * FROM pembinaan_internal_rules ORDER BY display_order;

-- Check for overlapping ranges (should return 0 rows)
SELECT a.id, a.poin_min, a.poin_max, b.id, b.poin_min, b.poin_max
FROM pembinaan_internal_rules a
JOIN pembinaan_internal_rules b ON a.id < b.id
WHERE (a.poin_min <= COALESCE(b.poin_max, 999999) 
   AND COALESCE(a.poin_max, 999999) >= b.poin_min);

-- Check JSON structure
SELECT id, pembina_roles FROM pembinaan_internal_rules;
```

---

## Performance Testing

### Test Query Performance:

```php
php artisan tinker

// Test dengan banyak siswa
$start = microtime(true);
$rulesEngine = new \App\Services\PelanggaranRulesEngine();
$siswaList = $rulesEngine->getSiswaPerluPembinaan();
$end = microtime(true);

echo "Query time: " . round(($end - $start) * 1000, 2) . " ms\n";
echo "Total siswa: " . $siswaList->count() . "\n";
```

**Expected:**
- Query time < 500ms untuk 100 siswa
- Query time < 2000ms untuk 1000 siswa

---

## Security Testing

### Access Control:
1. **Test sebagai Guru** (bukan Operator):
   - Akses `/pembinaan-internal-rules`
   - Expected: 403 Forbidden atau redirect

2. **Test sebagai Wali Murid**:
   - Akses `/pembinaan-internal-rules`
   - Expected: 403 Forbidden atau redirect

3. **Test tanpa login**:
   - Akses `/pembinaan-internal-rules`
   - Expected: Redirect ke login page

---

## Regression Testing

### Verify Existing Features Still Work:
- [ ] Frequency Rules masih berfungsi normal
- [ ] Pencatatan pelanggaran tidak terpengaruh
- [ ] Surat pemanggilan masih generate dengan benar
- [ ] Dashboard tidak error
- [ ] Riwayat pelanggaran masih bisa diakses

---

## Known Issues & Limitations

1. **Performance**: Method `getSiswaPerluPembinaan()` melakukan loop untuk setiap siswa. Untuk sekolah dengan >1000 siswa, pertimbangkan caching atau query optimization.

2. **Real-time Update**: Rekomendasi pembinaan tidak otomatis update di dashboard. Perlu refresh manual atau implement WebSocket/polling.

3. **Notification**: Belum ada notifikasi otomatis ke pembina saat siswa masuk threshold baru.

---

## Next Steps (Future Enhancements)

1. **Dashboard Widget**: Tampilkan siswa yang perlu pembinaan di dashboard Operator/Waka
2. **Export Functionality**: Export list siswa per range poin ke CSV/PDF
3. **Notification System**: Kirim notifikasi ke pembina saat siswa masuk threshold
4. **History Tracking**: Track perubahan rules dengan audit trail
5. **Bulk Actions**: Update multiple rules sekaligus

---

## Conclusion

Fitur Pembinaan Internal Rules Management sudah berhasil diimplementasi dengan:
- ✅ CRUD functionality lengkap
- ✅ Validation untuk prevent overlap
- ✅ Integration dengan PelanggaranRulesEngine
- ✅ Clean architecture & separation of concerns
- ✅ User-friendly UI dengan modal-based forms

Sistem siap digunakan untuk production setelah semua test scenarios di atas passed.

---

**Last Updated**: 2025-12-07  
**Tested By**: [Your Name]  
**Status**: Ready for UAT
