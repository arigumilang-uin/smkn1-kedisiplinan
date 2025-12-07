# Testing Guide: Flexible Surat Panggilan System

## Overview

Dokumen ini berisi panduan testing untuk sistem surat panggilan yang fleksibel. Sistem ini memungkinkan operator untuk menentukan pembina yang terlibat dalam penanganan pelanggaran tanpa terikat pada hierarchy yang hardcoded.

---

## Prerequisites

### 1. Data Setup
Pastikan data berikut sudah ada di database:

**Users dengan NIP:**
```sql
-- Update NIP untuk semua pembina
UPDATE users SET nip = '19730322 200012 2 002' WHERE nama = 'SALMIAH, S.Pd.MM'; -- Kepala Sekolah
UPDATE users SET nip = '19850615 200903 1 001' WHERE role_id = (SELECT id FROM roles WHERE nama_role = 'Wali Kelas');
UPDATE users SET nip = '19900420 201405 2 002' WHERE role_id = (SELECT id FROM roles WHERE nama_role = 'Kaprodi');
UPDATE users SET nip = '19880310 201203 1 003' WHERE role_id = (SELECT id FROM roles WHERE nama_role = 'Waka Kesiswaan');
UPDATE users SET nip = '19920725 201506 2 004' WHERE role_id = (SELECT id FROM roles WHERE nama_role = 'Waka Sarana');
```

**Frequency Rules dengan Berbagai Kombinasi Pembina:**
- Atribut 10+x: Wali Kelas (1 pembina)
- Alfa 4+x: Wali Kelas + Kaprodi (2 pembina)
- Merokok 1x: Wali Kelas + Kaprodi + Waka Kesiswaan (3 pembina)
- Narkoba 1x: Wali Kelas + Kaprodi + Waka Kesiswaan + Kepala Sekolah (4 pembina)

---

## Test Scenarios

### Scenario 1: Surat dengan 1 Pembina (Wali Kelas)
**Setup:**
1. Login sebagai Guru/Wali Kelas
2. Catat pelanggaran "Atribut/Seragam Tidak Lengkap" untuk siswa tertentu sebanyak 10x

**Expected Result:**
- TindakLanjut dibuat dengan status "Baru"
- SuratPanggilan dibuat dengan:
  - `tipe_surat`: "Surat 1"
  - `pembina_data`: `[{"jabatan": "Wali Kelas", "nama": "...", "nip": "..."}]`
  - `tanggal_pertemuan`: 3 hari dari sekarang
  - `waktu_pertemuan`: "09:00"
  - `keperluan`: "Panggilan orang tua"

**Verification:**
1. Buka halaman TindakLanjut untuk siswa tersebut
2. Klik "Cetak Surat"
3. Verify PDF:
   - Header: BIDANG KEAHLIAN sesuai jurusan siswa
   - Nomor surat: format `DRAFT/[random]/421.5-SMKN 1 LD/[tahun]`
   - Data siswa: nama, kelas/jurusan
   - Jadwal pertemuan: tanggal, waktu, tempat, keperluan
   - Tanda tangan: **HANYA Wali Kelas** (1 kolom, kiri)
   - NO "Mengetahui Kepala Sekolah" section

---

### Scenario 2: Surat dengan 2 Pembina (Wali Kelas + Kaprodi)
**Setup:**
1. Login sebagai Guru/Wali Kelas
2. Catat pelanggaran "Alfa" untuk siswa tertentu sebanyak 4x

**Expected Result:**
- TindakLanjut dibuat dengan status "Baru"
- SuratPanggilan dibuat dengan:
  - `tipe_surat`: "Surat 2"
  - `pembina_data`: `[{"jabatan": "Wali Kelas", ...}, {"jabatan": "Kaprodi", ...}]`

**Verification:**
1. Cetak surat
2. Verify PDF:
   - Tanda tangan: **Wali Kelas (kiri) + Kaprodi (kanan)** (2 kolom, 50% width each)
   - NO "Mengetahui Kepala Sekolah" section

---

### Scenario 3: Surat dengan 3 Pembina (Wali Kelas + Kaprodi + Waka Kesiswaan)
**Setup:**
1. Login sebagai Guru
2. Catat pelanggaran "Merokok" untuk siswa tertentu (1x langsung trigger)

**Expected Result:**
- TindakLanjut dibuat dengan status "Menunggu Persetujuan" (karena Surat 3)
- SuratPanggilan dibuat dengan:
  - `tipe_surat`: "Surat 3"
  - `pembina_data`: `[{"jabatan": "Wali Kelas", ...}, {"jabatan": "Kaprodi", ...}, {"jabatan": "Waka Kesiswaan", ...}]`

**Verification:**
1. Login sebagai Kepala Sekolah
2. Approve kasus tersebut
3. Cetak surat
4. Verify PDF:
   - Tanda tangan: **Wali Kelas + Kaprodi + Waka Kesiswaan** (3 kolom, 33% width each)
   - NO "Mengetahui Kepala Sekolah" section (karena Kepsek tidak dipilih di frequency rules)

---

### Scenario 4: Surat dengan 4 Pembina (Include Kepala Sekolah)
**Setup:**
1. Login sebagai Operator Sekolah
2. Buat frequency rule baru untuk "Narkoba" dengan pembina: Wali Kelas + Kaprodi + Waka Kesiswaan + Kepala Sekolah
3. Login sebagai Guru
4. Catat pelanggaran "Narkoba" untuk siswa tertentu

**Expected Result:**
- TindakLanjut dibuat dengan status "Menunggu Persetujuan"
- SuratPanggilan dibuat dengan:
  - `tipe_surat`: "Surat 4"
  - `pembina_data`: 4 pembina (Wali Kelas, Kaprodi, Waka Kesiswaan, Kepala Sekolah)

**Verification:**
1. Approve dan cetak surat
2. Verify PDF:
   - Tanda tangan: **4 pembina** (4 kolom, 25% width each)
   - Kepala Sekolah ditampilkan di kolom ke-4 (BUKAN di section "Mengetahui")
   - Semua pembina punya format yang sama (jabatan, nama, NIP)

---

### Scenario 5: Kombinasi Custom (Waka Sarana + Kepala Sekolah)
**Setup:**
1. Login sebagai Operator Sekolah
2. Buat frequency rule baru untuk "Merusak Fasilitas Sekolah" dengan pembina: Waka Sarana + Kepala Sekolah
3. Login sebagai Guru
4. Catat pelanggaran "Merusak Fasilitas Sekolah"

**Expected Result:**
- SuratPanggilan dibuat dengan pembina: Waka Sarana + Kepala Sekolah

**Verification:**
1. Cetak surat
2. Verify PDF:
   - Tanda tangan: **Waka Sarana (kiri) + Kepala Sekolah (kanan)** (2 kolom)
   - NO Wali Kelas atau Kaprodi (karena tidak dipilih di frequency rules)

---

### Scenario 6: Eskalasi Surat (Surat 1 → Surat 2)
**Setup:**
1. Siswa sudah punya kasus aktif dengan Surat 1 (Atribut 10x)
2. Catat pelanggaran baru yang trigger Surat 2 (Alfa 4x)

**Expected Result:**
- Kasus yang sama di-update (eskalasi)
- SuratPanggilan di-update dengan:
  - `tipe_surat`: "Surat 2"
  - `pembina_data`: Updated dengan pembina dari Surat 2

**Verification:**
1. Cetak surat
2. Verify PDF:
   - Tanda tangan: **Wali Kelas + Kaprodi** (bukan hanya Wali Kelas lagi)

---

### Scenario 7: Backward Compatibility (No pembina_data)
**Setup:**
1. Buat SuratPanggilan manual di database tanpa `pembina_data` (simulate old data)

**Expected Result:**
- Template fallback ke Wali Kelas saja

**Verification:**
1. Cetak surat
2. Verify PDF:
   - Tanda tangan: **Wali Kelas** (fallback)
   - No error/crash

---

### Scenario 8: Pembina Tanpa NIP
**Setup:**
1. Hapus NIP dari salah satu pembina:
   ```sql
   UPDATE users SET nip = NULL WHERE role_id = (SELECT id FROM roles WHERE nama_role = 'Wali Kelas') LIMIT 1;
   ```
2. Catat pelanggaran yang melibatkan Wali Kelas tersebut

**Expected Result:**
- SuratPanggilan tetap dibuat dengan `pembina_data` (NIP = null)

**Verification:**
1. Cetak surat
2. Verify PDF:
   - Tanda tangan Wali Kelas: nama ada, NIP diganti dengan `(...................................)`

---

## Database Verification

### Check pembina_data Structure
```sql
SELECT 
    sp.id,
    sp.tipe_surat,
    sp.pembina_data,
    sp.tanggal_pertemuan,
    sp.waktu_pertemuan,
    sp.keperluan,
    s.nama_siswa
FROM surat_panggilan sp
JOIN tindak_lanjut tl ON sp.tindak_lanjut_id = tl.id
JOIN siswa s ON tl.siswa_id = s.id
ORDER BY sp.created_at DESC
LIMIT 10;
```

**Expected Output:**
```json
{
  "pembina_data": [
    {
      "jabatan": "Wali Kelas",
      "nama": "John Doe",
      "nip": "19850615 200903 1 001"
    },
    {
      "jabatan": "Kaprodi",
      "nama": "Jane Smith",
      "nip": "19900420 201405 2 002"
    }
  ]
}
```

---

## Edge Cases

### Edge Case 1: Pembina Tidak Ditemukan
**Setup:**
1. Hapus Wali Kelas dari kelas tertentu:
   ```sql
   UPDATE kelas SET wali_kelas_user_id = NULL WHERE id = 1;
   ```
2. Catat pelanggaran untuk siswa di kelas tersebut

**Expected Result:**
- `pembina_data` tidak include Wali Kelas (atau include dengan nama/NIP null)
- System tidak crash

---

### Edge Case 2: Frequency Rule Tanpa Pembina
**Setup:**
1. Buat frequency rule dengan `pembina_roles` = `[]` (empty array)

**Expected Result:**
- `getSuratType()` return null (tidak trigger surat)
- Tidak ada SuratPanggilan dibuat

---

### Edge Case 3: Pembina "Semua Guru & Staff"
**Setup:**
1. Buat frequency rule dengan pembina "Semua Guru & Staff"

**Expected Result:**
- `getSuratType()` return null (tidak trigger surat formal)
- Tidak ada SuratPanggilan dibuat (pembinaan ditempat saja)

---

## Performance Testing

### Test 1: Batch Processing
**Setup:**
1. Catat 10 pelanggaran sekaligus untuk 1 siswa

**Expected Result:**
- `buildPembinaData()` dipanggil 1x saja (tidak 10x)
- Query pembina di-cache atau di-eager load

**Verification:**
- Check query log: `DB::enableQueryLog()`
- Verify jumlah query ke tabel `users` minimal

---

### Test 2: Large pembina_data
**Setup:**
1. Buat frequency rule dengan 5+ pembina

**Expected Result:**
- JSON `pembina_data` tersimpan dengan benar
- PDF generation tidak timeout

---

## Checklist

### Functional Testing
- [ ] Scenario 1: Surat 1 pembina (Wali Kelas)
- [ ] Scenario 2: Surat 2 pembina (Wali Kelas + Kaprodi)
- [ ] Scenario 3: Surat 3 pembina (Wali Kelas + Kaprodi + Waka)
- [ ] Scenario 4: Surat 4 pembina (Include Kepala Sekolah)
- [ ] Scenario 5: Kombinasi custom (Waka Sarana + Kepsek)
- [ ] Scenario 6: Eskalasi surat
- [ ] Scenario 7: Backward compatibility
- [ ] Scenario 8: Pembina tanpa NIP

### Edge Cases
- [ ] Pembina tidak ditemukan
- [ ] Frequency rule tanpa pembina
- [ ] Pembina "Semua Guru & Staff"

### Database Verification
- [ ] pembina_data structure correct
- [ ] tanggal_pertemuan set correctly (3 days from now)
- [ ] waktu_pertemuan set correctly (09:00)
- [ ] keperluan populated

### PDF Verification
- [ ] Header with correct BIDANG KEAHLIAN
- [ ] Nomor surat format correct
- [ ] Data siswa complete
- [ ] Jadwal pertemuan complete
- [ ] Signature layout flexible (1-4 columns)
- [ ] NO "Mengetahui Kepala Sekolah" section
- [ ] NIP displayed or placeholder if null

### Performance
- [ ] Batch processing efficient
- [ ] Large pembina_data handled
- [ ] PDF generation < 2s

---

## Troubleshooting

### Issue: pembina_data is null
**Cause:** `buildPembinaData()` tidak dipanggil atau return empty array

**Solution:**
1. Check `evaluateFrequencyRules()` return `pembina_roles`
2. Check `buatAtauUpdateTindakLanjut()` pass `pembinaRoles` parameter
3. Check `SuratPanggilanService::buildPembinaData()` logic

---

### Issue: PDF layout broken
**Cause:** Template CSS tidak support jumlah pembina tertentu

**Solution:**
1. Check `cols-X` class applied correctly
2. Verify `ttd-cell` width calculation
3. Test with different pembina counts (1, 2, 3, 4, 5+)

---

### Issue: Pembina tidak ditemukan
**Cause:** Relasi database tidak di-load atau user tidak ada

**Solution:**
1. Check `loadMissing()` di `SuratPanggilanService`
2. Verify foreign keys di database (wali_kelas_user_id, kaprodi_user_id)
3. Check role query untuk Waka/Kepsek

---

## Success Criteria

✅ **System is ready for production if:**
1. All functional scenarios pass
2. All edge cases handled gracefully
3. Database structure correct
4. PDF generation works for all pembina combinations
5. Performance acceptable (< 2s for PDF generation)
6. No "Mengetahui Kepala Sekolah" section in any surat
7. Backward compatibility maintained

---

**Last Updated:** 2025-12-07  
**Status:** Ready for Testing
