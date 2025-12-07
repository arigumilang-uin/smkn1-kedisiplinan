# Testing Guide: NIP/NUPTK Implementation

## Overview

Sistem tanda pengenal kepegawaian untuk guru dengan 2 opsi: NIP/NI PPPK dan NUPTK. Keduanya opsional dan bisa diisi bersamaan.

---

## Database Changes

### Migration
```sql
ALTER TABLE users 
ADD COLUMN nuptk VARCHAR(18) NULL COMMENT 'NUPTK (18 digit)' AFTER nip;
```

**Status:** ✅ Migrated successfully

---

## Priority Logic

### Display Priority di Surat:
```
NIP > NUPTK > Placeholder
```

**Contoh:**
- User punya NIP + NUPTK → Display: NIP
- User hanya punya NUPTK → Display: NUPTK
- User tidak punya keduanya → Display: `(...................................)`

---

## Test Scenarios

### Scenario 1: Create User dengan NIP saja
**Steps:**
1. Login sebagai Operator Sekolah
2. Buka "Manajemen User" → "Tambah User"
3. Isi form:
   - Role: Wali Kelas
   - Email: walikelas@test.com
   - NIP: 197303222000122002
   - NUPTK: (kosong)
4. Submit

**Expected Result:**
- User created successfully
- `nip` = "197303222000122002"
- `nuptk` = NULL

**Verification:**
```sql
SELECT nama, nip, nuptk FROM users WHERE email = 'walikelas@test.com';
```

---

### Scenario 2: Create User dengan NUPTK saja
**Steps:**
1. Tambah user baru
2. Isi form:
   - Role: Kaprodi
   - Email: kaprodi@test.com
   - NIP: (kosong)
   - NUPTK: 123456789012345678
3. Submit

**Expected Result:**
- User created successfully
- `nip` = NULL
- `nuptk` = "123456789012345678"

---

### Scenario 3: Create User dengan NIP + NUPTK
**Steps:**
1. Tambah user baru
2. Isi form:
   - Role: Waka Kesiswaan
   - Email: waka@test.com
   - NIP: 198803102012031003
   - NUPTK: 987654321098765432
3. Submit

**Expected Result:**
- User created successfully
- `nip` = "198803102012031003"
- `nuptk` = "987654321098765432"

---

### Scenario 4: Create User tanpa NIP/NUPTK
**Steps:**
1. Tambah user baru
2. Isi form:
   - Role: Guru
   - Email: guru@test.com
   - NIP: (kosong)
   - NUPTK: (kosong)
3. Submit

**Expected Result:**
- User created successfully
- `nip` = NULL
- `nuptk` = NULL
- No validation error (keduanya opsional)

---

### Scenario 5: Update User - Tambah NIP/NUPTK
**Steps:**
1. Edit user yang sudah ada (tanpa NIP/NUPTK)
2. Tambahkan:
   - NIP: 197303222000122002
   - NUPTK: 123456789012345678
3. Submit

**Expected Result:**
- User updated successfully
- `nip` dan `nuptk` tersimpan

---

### Scenario 6: Update User - Hapus NIP/NUPTK
**Steps:**
1. Edit user yang punya NIP/NUPTK
2. Kosongkan kedua field
3. Submit

**Expected Result:**
- User updated successfully
- `nip` = NULL
- `nuptk` = NULL

---

### Scenario 7: Validation - NIP kurang dari 18 digit
**Steps:**
1. Tambah user baru
2. Isi NIP: 12345 (kurang dari 18 digit)
3. Submit

**Expected Result:**
- Validation error: "The nip must be 18 characters."
- Form tidak submit

---

### Scenario 8: Validation - NUPTK lebih dari 18 digit
**Steps:**
1. Tambah user baru
2. Isi NUPTK: 1234567890123456789 (19 digit)
3. Submit

**Expected Result:**
- Validation error: "The nuptk must be 18 characters."
- Form tidak submit

---

### Scenario 9: Display di Surat - User dengan NIP + NUPTK
**Setup:**
1. User Wali Kelas punya NIP + NUPTK
2. Catat pelanggaran yang trigger surat dengan pembina Wali Kelas
3. Cetak surat

**Expected Result:**
- Surat menampilkan NIP (bukan NUPTK)
- Format: `NIP. 197303222000122002`

---

### Scenario 10: Display di Surat - User hanya NUPTK
**Setup:**
1. User Wali Kelas hanya punya NUPTK (NIP kosong)
2. Catat pelanggaran yang trigger surat
3. Cetak surat

**Expected Result:**
- Surat menampilkan NUPTK
- Format: `NIP. 123456789012345678` (label tetap "NIP" untuk konsistensi)

---

### Scenario 11: Display di Surat - User tanpa NIP/NUPTK
**Setup:**
1. User Wali Kelas tidak punya NIP/NUPTK
2. Catat pelanggaran yang trigger surat
3. Cetak surat

**Expected Result:**
- Surat menampilkan placeholder
- Format: `(...................................)`

---

## UI Verification

### Create Form
**Check:**
- [ ] Section "Tanda Pengenal Kepegawaian (Opsional)" ada
- [ ] Input NIP/NI PPPK (18 digit) ada
- [ ] Input NUPTK (18 digit) ada
- [ ] Alert info: "Digunakan untuk tanda tangan surat resmi dan dokumen administrasi"
- [ ] Placeholder text jelas
- [ ] maxlength="18" applied
- [ ] Tidak ada keterangan yang terlalu frontal/memaksa

### Edit Form
**Check:**
- [ ] Section "Tanda Pengenal Kepegawaian (Opsional)" ada
- [ ] Input NIP/NI PPPK pre-filled dengan data existing
- [ ] Input NUPTK pre-filled dengan data existing
- [ ] Alert info sama dengan create form
- [ ] Bisa update/hapus NIP/NUPTK

---

## Database Verification

### Check Data Structure
```sql
DESCRIBE users;
-- Expected: nip VARCHAR(18) NULL, nuptk VARCHAR(18) NULL
```

### Check Sample Data
```sql
SELECT id, nama, role_id, nip, nuptk 
FROM users 
WHERE nip IS NOT NULL OR nuptk IS NOT NULL
LIMIT 10;
```

### Check Priority Logic
```sql
-- User dengan NIP + NUPTK
SELECT 
    nama,
    nip,
    nuptk,
    COALESCE(nip, nuptk, 'Tidak ada') AS display_value
FROM users
WHERE id = 1;
```

---

## Integration Test: Surat Panggilan

### Test Flow:
1. **Setup User:**
   ```sql
   UPDATE users SET nip = '197303222000122002', nuptk = '123456789012345678' WHERE id = 1;
   ```

2. **Catat Pelanggaran:**
   - Login sebagai Guru
   - Catat pelanggaran "Atribut 10x" untuk siswa tertentu

3. **Check SuratPanggilan:**
   ```sql
   SELECT pembina_data FROM surat_panggilan ORDER BY created_at DESC LIMIT 1;
   ```
   
   **Expected:**
   ```json
   [
     {
       "jabatan": "Wali Kelas",
       "nama": "John Doe",
       "nip": "197303222000122002"
     }
   ]
   ```
   
   **Note:** `nip` field berisi NIP (bukan NUPTK) karena priority logic

4. **Cetak Surat:**
   - Buka TindakLanjut
   - Klik "Cetak Surat"
   - Verify PDF menampilkan: `NIP. 197303222000122002`

---

## Edge Cases

### Edge Case 1: NIP dengan spasi
**Input:** `1973 0322 2000 1220 02`

**Expected:**
- Validation pass (18 characters including spaces)
- Stored as-is
- Display di surat: `NIP. 1973 0322 2000 1220 02`

**Note:** Jika ingin strip spasi, tambahkan logic di controller:
```php
$request->merge([
    'nip' => str_replace(' ', '', $request->nip),
    'nuptk' => str_replace(' ', '', $request->nuptk),
]);
```

---

### Edge Case 2: NIP dengan karakter non-numeric
**Input:** `1973-0322-2000-1220`

**Expected:**
- Validation pass (18 characters)
- Stored as-is

**Note:** Jika ingin validasi hanya numeric, tambahkan rule:
```php
'nip' => 'nullable|string|size:18|regex:/^[0-9]+$/',
```

---

### Edge Case 3: User dengan role Wali Murid
**Question:** Apakah Wali Murid perlu NIP/NUPTK?

**Answer:** TIDAK. Wali Murid tidak terlibat dalam surat panggilan sebagai pembina.

**UI Behavior:** Field tetap ditampilkan tapi tidak wajib diisi.

---

## Performance Test

### Test 1: Bulk Update NIP/NUPTK
```sql
UPDATE users 
SET nip = CONCAT('1973032220001220', LPAD(id, 2, '0'))
WHERE role_id IN (SELECT id FROM roles WHERE nama_role IN ('Wali Kelas', 'Kaprodi', 'Waka Kesiswaan', 'Waka Sarana', 'Kepala Sekolah'));
```

**Expected:** Update < 1s for 100 users

---

### Test 2: Query Performance
```sql
EXPLAIN SELECT * FROM users WHERE nip IS NOT NULL OR nuptk IS NOT NULL;
```

**Expected:** No full table scan (use index if needed)

---

## Checklist

### Database
- [x] Migration created and run
- [x] Column `nuptk` added after `nip`
- [x] Both columns nullable
- [x] Both columns VARCHAR(18)

### Model
- [x] `nuptk` added to fillable array
- [x] No casting needed (string)

### Controller
- [x] Validation added for `nip` (size:18)
- [x] Validation added for `nuptk` (size:18)
- [x] Store method updated
- [x] Update method updated

### Service
- [x] Priority logic implemented (NIP > NUPTK > null)
- [x] `buildPembinaData()` updated

### Views
- [x] Create form updated with NIP/NUPTK fields
- [x] Edit form updated with NIP/NUPTK fields
- [x] Alert info added (halus, tidak frontal)
- [x] Placeholder text clear
- [x] maxlength="18" applied

### Template
- [x] Display logic updated (NIP > NUPTK > placeholder)
- [x] Format consistent: `NIP. [value]`

### Testing
- [ ] All scenarios tested
- [ ] Edge cases handled
- [ ] Integration test passed
- [ ] Performance acceptable

---

## Success Criteria

✅ **System is ready if:**
1. User dapat create/update dengan NIP, NUPTK, atau keduanya
2. Validation 18 digit works
3. Priority logic works (NIP > NUPTK)
4. Surat panggilan display correct tanda pengenal
5. UI halus dan tidak memaksa
6. No breaking changes

---

**Last Updated:** 2025-12-07  
**Status:** Ready for Testing
