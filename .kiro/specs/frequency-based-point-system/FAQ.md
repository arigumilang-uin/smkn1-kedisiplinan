# FAQ: Frequency-Based Point System

## General Questions

### Q1: Apa perbedaan utama antara sistem lama dan sistem baru?

**A:** Perbedaan utama ada di cara pemberian poin:

**Sistem Lama:**
- Poin diberikan SETIAP KALI pelanggaran tercatat
- Contoh: Alfa 4x = 4 × 25 = **100 poin**

**Sistem Baru:**
- Poin diberikan HANYA saat threshold frekuensi tercapai
- Contoh: Alfa 4x = 25 poin (threshold 1-3) + 25 poin (threshold 4) = **50 poin**

Sistem baru lebih akurat sesuai dengan tata tertib sekolah yang sebenarnya.

---

### Q2: Apakah data pelanggaran existing akan berubah?

**A:** TIDAK. Data riwayat pelanggaran existing tidak akan diubah. Hanya logic evaluasi kedepannya yang berubah. Jika Operator ingin recalculate poin siswa berdasarkan frequency rules baru, bisa dilakukan secara manual (optional).

---

### Q3: Apakah semua pelanggaran menggunakan frequency rules?

**A:** TIDAK. Hanya pelanggaran yang memiliki multiple thresholds yang menggunakan frequency rules. Pelanggaran berat (frekuensi = 1) tetap langsung dapat poin seperti sebelumnya (backward compatibility).

**Contoh:**
- ✅ Alfa (1-3 → 25 poin, 4+ → 25 poin) → Menggunakan frequency rules
- ✅ Atribut (1-9 → 0 poin, 10+ → 5 poin) → Menggunakan frequency rules
- ❌ Merokok (1x → 100 poin) → TIDAK menggunakan frequency rules (langsung)

---

### Q4: Apa itu "Pembinaan Internal"? Apakah trigger surat pemanggilan?

**A:** Pembinaan Internal adalah **konseling/teguran langsung kepada siswa** tanpa melibatkan orang tua. Ini adalah **rekomendasi siapa yang harus melakukan konseling** berdasarkan total poin akumulasi siswa.

**PENTING: Pembinaan Internal TIDAK trigger surat pemanggilan otomatis!**

**Range Pembinaan Internal (Rekomendasi Konseling):**
- 0-50 poin: Wali Kelas (konseling ringan)
- 55-100 poin: Wali Kelas + Kaprodi (monitoring ketat)
- 105-300 poin: Wali Kelas + Kaprodi + Waka (pembinaan intensif)
- 305-500 poin: Wali Kelas + Kaprodi + Waka + Kepsek (pembinaan kritis)
- >500 poin: Dikembalikan kepada orang tua (siswa tidak dapat melanjutkan)

**Surat Pemanggilan Orang Tua (Sistem Terpisah):**

Surat pemanggilan HANYA trigger jika siswa melakukan **pelanggaran baru** dengan sanksi:
- "Panggilan orang tua", ATAU
- "Dikembalikan kepada orang tua"

**Contoh:**
- Siswa punya 60 poin → Rekomendasi: Wali Kelas + Kaprodi konseling → **TIDAK ADA SURAT**
- Siswa melakukan Alfa ke-4 → Sanksi: "Panggilan orang tua" → **SURAT 1 dibuat**

---

### Q5: Bagaimana cara menentukan tipe surat (Surat 1, 2, 3, 4)?

**A:** Tipe surat ditentukan berdasarkan **jumlah pembina yang terlibat**, bukan hanya poin:

- **Surat 1**: Wali Kelas
- **Surat 2**: Wali Kelas + Kaprodi
- **Surat 3**: Wali Kelas + Kaprodi + Waka Kesiswaan
- **Surat 4**: Wali Kelas + Kaprodi + Waka + Kepsek

**Contoh:**
- Alfa 4x → Sanksi: "Panggilan orang tua", Pembina: Wali Kelas → **Surat 1**
- Merokok 1x → Sanksi: "Panggilan orang tua", Pembina: Wali Kelas + Kaprodi → **Surat 2**
- Alkohol 1x → Sanksi: "Panggilan orang tua", Pembina: Wali Kelas + Kaprodi + Waka → **Surat 3**
- Narkoba 1x → Sanksi: "Dikembalikan kepada orang tua", Pembina: Semua → **Surat 4**

---

## For Operators

### Q6: Bagaimana cara mengelola frequency rules?

**A:** 
1. Login sebagai Operator Sekolah
2. Klik menu "Kelola Frequency Rules" di sidebar
3. Pilih jenis pelanggaran yang ingin dikelola
4. Klik "Kelola Rules"
5. Tambah/edit/hapus frequency rules sesuai kebutuhan

**Validasi:**
- Threshold tidak boleh overlap
- `frequency_min` harus >= 1
- `frequency_max` harus >= `frequency_min` atau NULL (open-ended)
- `poin` harus >= 0

---

### Q7: Apa yang terjadi jika saya mengubah frequency rules?

**A:** Perubahan frequency rules akan berlaku untuk pencatatan pelanggaran KEDEPANNYA. Data existing tidak akan berubah otomatis. Jika ingin recalculate poin siswa, bisa dilakukan secara manual (optional).

**Contoh:**
- Sebelum: Alfa 4x → Surat 1
- Setelah diubah: Alfa 5x → Surat 1
- Siswa yang sudah punya Alfa 4x → Tetap Surat 1 (tidak berubah)
- Siswa baru yang Alfa 4x → Belum Surat 1 (perlu 5x)

---

### Q8: Bagaimana cara menambah role Waka Sarana untuk user?

**A:**
1. Login sebagai Operator Sekolah
2. Klik menu "Kelola User"
3. Pilih user yang ingin dijadikan Waka Sarana
4. Edit role menjadi "Waka Sarana"
5. Simpan

User tersebut akan otomatis punya akses ke dashboard Waka Sarana.

---

## For Guru/Wali Kelas

### Q9: Bagaimana cara melihat preview frequency saat mencatat pelanggaran?

**A:**
1. Login sebagai Guru/Wali Kelas
2. Klik menu "Catat Pelanggaran"
3. Pilih siswa dan jenis pelanggaran
4. Preview frequency akan muncul otomatis di bawah form
5. Preview menampilkan:
   - Frekuensi saat ini
   - Warning jika threshold akan tercapai
   - Poin yang akan ditambahkan
   - Sanksi yang akan ditrigger

---

### Q10: Apa yang harus saya lakukan jika preview menampilkan warning?

**A:** Warning muncul jika threshold akan tercapai. Ini adalah informasi untuk Anda agar tahu bahwa:
- Poin akan ditambahkan ke siswa
- Sanksi akan ditrigger (misalnya: panggilan orang tua)
- Surat pemanggilan mungkin akan dibuat

Anda tetap bisa melanjutkan pencatatan pelanggaran. Warning hanya untuk informasi, bukan untuk mencegah pencatatan.

---

### Q11: Apakah saya bisa mencatat pelanggaran untuk siswa di kelas/jurusan lain?

**A:** YA. Semua Guru/Wali Kelas/Waka/Kaprodi bisa mencatat pelanggaran untuk SEMUA siswa, tidak terbatas pada kelas/jurusan binaan.

**Namun:**
- Wali Kelas hanya bisa **lihat** riwayat siswa di kelasnya
- Kaprodi hanya bisa **lihat** riwayat siswa di jurusannya
- Waka & Kepsek bisa **lihat** semua riwayat
- Guru biasa hanya bisa **lihat/edit/hapus** riwayat yang DIA catat sendiri

---

## For Waka Sarana

### Q12: Apa tugas Waka Sarana di sistem ini?

**A:** Waka Sarana fokus pada pelanggaran fasilitas. Tugas utama:
1. Monitor pelanggaran "Merusak Fasilitas" di dashboard
2. Catat pelanggaran fasilitas yang ditemukan
3. Follow-up dengan siswa yang merusak fasilitas
4. Koordinasi dengan Wali Kelas untuk pembinaan

**Dashboard Waka Sarana menampilkan:**
- Total pelanggaran fasilitas
- Pelanggaran fasilitas bulan ini
- Riwayat pelanggaran fasilitas terbaru (10 records)
- Riwayat yang dicatat oleh Waka Sarana sendiri (5 records)

---

### Q13: Apakah Waka Sarana hanya bisa catat pelanggaran fasilitas?

**A:** TIDAK. Waka Sarana bisa catat SEMUA jenis pelanggaran, sama seperti Guru. Namun dashboard Waka Sarana fokus pada pelanggaran fasilitas untuk memudahkan monitoring.

---

### Q14: Apakah Waka Sarana bisa edit/hapus riwayat pelanggaran orang lain?

**A:** TIDAK. Waka Sarana hanya bisa edit/hapus riwayat yang DIA catat sendiri. Ini sama dengan permission Guru.

**Hanya Operator Sekolah yang bisa edit/hapus SEMUA riwayat** untuk data integrity.

---

## Technical Questions

### Q15: Bagaimana sistem menghitung frekuensi pelanggaran?

**A:** Sistem menghitung frekuensi dengan query:
```sql
SELECT COUNT(*) 
FROM riwayat_pelanggaran 
WHERE siswa_id = ? AND jenis_pelanggaran_id = ?
```

Frekuensi dihitung dari SEMUA riwayat pelanggaran siswa untuk jenis pelanggaran tertentu, tidak ada batasan waktu.

---

### Q16: Bagaimana sistem menentukan apakah threshold baru tercapai?

**A:** Sistem membandingkan frekuensi saat ini dengan frekuensi sebelumnya:

**Contoh: Alfa dengan rules:**
- Rule 1: Frekuensi 1-3 → 25 poin
- Rule 2: Frekuensi 4+ → 25 poin

**Saat Alfa ke-4 dicatat:**
- Current frequency: 4
- Previous frequency: 3
- Rule untuk frequency 4: Rule 2
- Rule untuk frequency 3: Rule 1
- Rule berbeda? YA → Threshold baru tercapai → Tambah 25 poin

**Saat Alfa ke-5 dicatat:**
- Current frequency: 5
- Previous frequency: 4
- Rule untuk frequency 5: Rule 2
- Rule untuk frequency 4: Rule 2
- Rule sama? YA → Masih di range yang sama → TIDAK tambah poin

---

### Q17: Bagaimana sistem handle pelanggaran tanpa frequency rules?

**A:** Pelanggaran tanpa frequency rules menggunakan logic lama (immediate accumulation):
- Poin diberikan langsung setiap kali pelanggaran tercatat
- Tidak ada threshold evaluation

Ini untuk backward compatibility, terutama untuk pelanggaran berat (frekuensi = 1).

---

### Q18: Apakah ada caching untuk frequency rules?

**A:** YA. Frequency rules di-cache dengan TTL 1 jam untuk performance. Cache akan di-invalidate otomatis saat:
- Operator menambah/edit/hapus frequency rules
- Sistem restart

Jika perlu clear cache manual, bisa jalankan: `php artisan cache:clear`

---

## Troubleshooting

### Q19: Preview frequency tidak muncul saat mencatat pelanggaran

**Possible Causes:**
1. JavaScript error di browser (check console)
2. AJAX endpoint tidak accessible (check network tab)
3. Pelanggaran tidak memiliki frequency rules (expected behavior)

**Solution:**
1. Refresh halaman
2. Clear browser cache
3. Check apakah pelanggaran memiliki frequency rules di halaman "Kelola Frequency Rules"

---

### Q20: Poin siswa tidak sesuai dengan expected

**Possible Causes:**
1. Frequency rules belum di-seed dengan benar
2. Pelanggaran tidak memiliki flag `has_frequency_rules = TRUE`
3. Cache belum di-clear setelah perubahan rules

**Solution:**
1. Check frequency rules di halaman "Kelola Frequency Rules"
2. Check flag `has_frequency_rules` di database
3. Clear cache: `php artisan cache:clear`
4. Recalculate poin siswa (manual)

---

### Q21: Surat pemanggilan tidak trigger padahal threshold tercapai

**Possible Causes:**
1. Sanksi tidak mencantumkan "Panggilan orang tua"
2. Flag `trigger_surat` di frequency rule = FALSE
3. Pembina roles tidak sesuai

**Solution:**
1. Check sanksi description di frequency rule
2. Check flag `trigger_surat` di frequency rule
3. Check pembina roles di frequency rule
4. Update frequency rule jika diperlukan

---

### Q22: Dashboard Waka Sarana tidak menampilkan data

**Possible Causes:**
1. Tidak ada pelanggaran fasilitas yang tercatat
2. Query filter terlalu strict
3. Permission issue

**Solution:**
1. Check apakah ada riwayat pelanggaran "Merusak Fasilitas" di database
2. Check query filter di `WakaSaranaDashboardController`
3. Check role user = "Waka Sarana"

---

## Migration Questions

### Q23: Apakah perlu downtime saat deployment?

**A:** YA, disarankan ada maintenance window singkat (5-10 menit) untuk:
1. Backup database
2. Run migrations
3. Run seeders
4. Clear cache
5. Restart services

---

### Q24: Bagaimana cara rollback jika ada masalah?

**A:** Rollback plan:
1. **Rollback Database:**
   ```sql
   DROP TABLE pelanggaran_frequency_rules;
   ALTER TABLE jenis_pelanggaran DROP COLUMN has_frequency_rules;
   DELETE FROM roles WHERE nama_role = 'Waka Sarana';
   ```

2. **Rollback Code:**
   ```bash
   git revert <commit-hash>
   php artisan cache:clear
   ```

3. **Restore Database (if needed):**
   ```bash
   mysql -u root -p database_name < backup.sql
   ```

---

### Q25: Apakah perlu training untuk user?

**A:** YA, disarankan training untuk:
1. **Operator**: Cara manage frequency rules
2. **Guru/Wali Kelas**: Cara menggunakan preview frequency
3. **Waka Sarana**: Cara menggunakan dashboard baru

Training bisa dilakukan via:
- Session tatap muka (1-2 jam)
- Video tutorial
- User documentation (FREQUENCY_BASED_POINT_SYSTEM.md)

---

## Contact & Support

### Q26: Siapa yang bisa saya hubungi jika ada masalah?

**A:**
- **Technical Issues**: Developer team
- **User Questions**: Operator Sekolah
- **Training**: Admin team
- **Bug Reports**: Developer team (via issue tracker)

---

### Q27: Dimana saya bisa menemukan dokumentasi lengkap?

**A:** Dokumentasi lengkap tersedia di:
1. **User Documentation**: `FREQUENCY_BASED_POINT_SYSTEM.md`
2. **Technical Documentation**: `design.md`, `tasks.md`
3. **Changelog**: `CHANGELOG.md`
4. **FAQ**: `FAQ.md` (this file)
5. **Tata Tertib Reference**: `TATA_TERTIB_REFERENCE.md`

Semua file tersedia di folder `.kiro/specs/frequency-based-point-system/`

---

**Last Updated**: 2025-12-06  
**Version**: 2.0.0 (Planned)
