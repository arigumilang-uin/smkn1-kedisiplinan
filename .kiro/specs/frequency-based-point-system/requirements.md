# Requirements Document: Frequency-Based Point System

## Introduction

Sistem poin pelanggaran saat ini memberikan poin **setiap kali** pelanggaran tercatat, yang tidak sesuai dengan tata tertib sekolah. Tata tertib sekolah menggunakan sistem **threshold frekuensi**, dimana poin hanya diberikan saat frekuensi pelanggaran mencapai threshold tertentu.

Perubahan ini akan mengubah fundamental logic dari Rules Engine untuk mengikuti tata tertib yang sebenarnya, serta menambahkan role baru **Waka Sarana** untuk menangani pelanggaran fasilitas.

## Glossary

- **Frequency Threshold**: Jumlah minimum pelanggaran yang harus tercapai sebelum poin diberikan
- **Frequency Rule**: Aturan yang mendefinisikan threshold, poin, dan sanksi untuk setiap jenis pelanggaran
- **Surat Pemanggilan**: Surat resmi untuk memanggil orang tua siswa ke sekolah
- **Pembinaan Internal**: Konseling/teguran langsung kepada siswa tanpa melibatkan orang tua
- **Waka Sarana**: Wakil Kepala Sekolah bidang Sarana & Prasarana

## Requirements

### Requirement 1: Frequency-Based Point Accumulation

**User Story:** Sebagai sistem, saya ingin memberikan poin berdasarkan threshold frekuensi, sehingga akumulasi poin akurat sesuai tata tertib.

#### Acceptance Criteria

1. WHEN siswa melakukan pelanggaran yang memiliki frequency rules, THEN sistem SHALL menghitung frekuensi total pelanggaran tersebut
2. WHEN frekuensi mencapai threshold yang didefinisikan, THEN sistem SHALL menambahkan poin sesuai rule
3. WHEN frekuensi belum mencapai threshold, THEN sistem SHALL NOT menambahkan poin
4. WHEN threshold sudah pernah tercapai sebelumnya, THEN sistem SHALL NOT menambahkan poin lagi untuk threshold yang sama
5. WHEN siswa mencapai threshold baru yang lebih tinggi, THEN sistem SHALL menambahkan poin untuk threshold baru tersebut

**Contoh:**
- Alfa 1x → 0 poin (threshold 1-3 belum tercapai)
- Alfa 2x → 0 poin (threshold 1-3 belum tercapai)
- Alfa 3x → +25 poin (threshold 1-3 tercapai)
- Alfa 4x → +25 poin (threshold 4 tercapai)
- Total: 50 poin

---

### Requirement 2: Frequency Rules Management

**User Story:** Sebagai Operator, saya ingin mengelola frequency rules untuk setiap jenis pelanggaran, sehingga sistem dapat mengikuti tata tertib yang berlaku.

#### Acceptance Criteria

1. WHEN Operator mengakses halaman jenis pelanggaran, THEN sistem SHALL menampilkan opsi untuk mengelola frequency rules
2. WHEN Operator menambah frequency rule, THEN sistem SHALL menyimpan threshold min/max, poin, sanksi, dan trigger surat
3. WHEN Operator mengubah frequency rule, THEN sistem SHALL memvalidasi bahwa threshold tidak overlap
4. WHEN Operator menghapus frequency rule, THEN sistem SHALL meminta konfirmasi
5. WHEN jenis pelanggaran memiliki frequency rules, THEN sistem SHALL menggunakan frequency-based logic

---

### Requirement 3: Surat Pemanggilan Based on Pembina

**User Story:** Sebagai sistem, saya ingin menentukan tipe surat berdasarkan pembina yang terlibat, sehingga surat pemanggilan sesuai dengan hierarki pembinaan.

#### Acceptance Criteria

1. WHEN sanksi mencantumkan "Panggilan orang tua" DAN pembina = Wali Kelas, THEN sistem SHALL membuat Surat 1
2. WHEN sanksi mencantumkan "Panggilan orang tua" DAN pembina = Wali Kelas + Kaprodi, THEN sistem SHALL membuat Surat 2
3. WHEN sanksi mencantumkan "Panggilan orang tua" DAN pembina = Wali Kelas + Kaprodi + Waka, THEN sistem SHALL membuat Surat 3
4. WHEN sanksi mencantumkan "Dikembalikan kepada orang tua", THEN sistem SHALL membuat Surat 4
5. WHEN sanksi TIDAK mencantumkan "Panggilan orang tua", THEN sistem SHALL NOT membuat surat

---

### Requirement 4: Pembinaan Internal Based on Akumulasi

**User Story:** Sebagai sistem, saya ingin menentukan pembina internal berdasarkan total akumulasi poin, sehingga siswa mendapat pembinaan yang sesuai.

#### Acceptance Criteria

1. WHEN total poin siswa 0-50, THEN sistem SHALL menandai pembinaan oleh Wali Kelas
2. WHEN total poin siswa 55-100, THEN sistem SHALL menandai pembinaan oleh Wali Kelas + Kaprodi
3. WHEN total poin siswa 105-300, THEN sistem SHALL menandai pembinaan oleh Wali Kelas + Kaprodi + Waka
4. WHEN total poin siswa 305-500, THEN sistem SHALL menandai pembinaan oleh Wali Kelas + Kaprodi + Waka + Kepsek
5. WHEN total poin siswa >500, THEN sistem SHALL menandai "Dikembalikan kepada orang tua"

**CATATAN:** Pembinaan internal adalah konseling, BUKAN trigger surat pemanggilan.

---

### Requirement 5: Role Waka Sarana

**User Story:** Sebagai Waka Sarana, saya ingin memiliki dashboard yang fokus pada pelanggaran fasilitas, sehingga saya dapat memonitor dan menangani kerusakan fasilitas dengan efektif.

#### Acceptance Criteria

1. WHEN Waka Sarana login, THEN sistem SHALL menampilkan dashboard dengan statistik pelanggaran fasilitas
2. WHEN Waka Sarana mengakses riwayat pelanggaran, THEN sistem SHALL menampilkan semua pelanggaran "Merusak Fasilitas"
3. WHEN Waka Sarana mencatat pelanggaran, THEN sistem SHALL mengizinkan pencatatan semua jenis pelanggaran (seperti Guru)
4. WHEN Waka Sarana mengedit/hapus riwayat, THEN sistem SHALL hanya mengizinkan riwayat yang DIA catat sendiri
5. WHEN Waka Sarana melihat detail siswa, THEN sistem SHALL menampilkan riwayat pelanggaran fasilitas siswa tersebut

---

### Requirement 6: Backward Compatibility for Pelanggaran Berat

**User Story:** Sebagai sistem, saya ingin tetap memberikan poin langsung untuk pelanggaran berat, sehingga tidak ada perubahan untuk pelanggaran yang sudah benar.

#### Acceptance Criteria

1. WHEN jenis pelanggaran adalah pelanggaran berat (frekuensi = 1), THEN sistem SHALL memberikan poin langsung
2. WHEN jenis pelanggaran TIDAK memiliki frequency rules, THEN sistem SHALL menggunakan logic lama (poin langsung)
3. WHEN pelanggaran berat tercatat, THEN sistem SHALL langsung trigger surat jika diperlukan
4. WHEN pelanggaran berat tercatat, THEN sistem SHALL langsung menambah akumulasi poin
5. WHEN sistem mengevaluasi pelanggaran, THEN sistem SHALL memeriksa flag `has_frequency_rules` untuk menentukan logic yang digunakan

---

### Requirement 7: Data Migration for Existing Records

**User Story:** Sebagai Operator, saya ingin data existing tetap valid setelah perubahan sistem, sehingga tidak ada data yang hilang atau corrupt.

#### Acceptance Criteria

1. WHEN migration dijalankan, THEN sistem SHALL membuat tabel `pelanggaran_frequency_rules`
2. WHEN migration dijalankan, THEN sistem SHALL populate frequency rules dari tata tertib
3. WHEN migration dijalankan, THEN sistem SHALL update flag `has_frequency_rules` untuk setiap jenis pelanggaran
4. WHEN migration dijalankan, THEN sistem SHALL menambah role `Waka Sarana`
5. WHEN migration dijalankan, THEN sistem SHALL membackup data existing sebelum perubahan

**CATATAN:** Data riwayat pelanggaran existing TIDAK diubah, hanya logic evaluasi kedepannya yang berubah.

---

### Requirement 8: Operator Management for Frequency Rules

**User Story:** Sebagai Operator, saya ingin dapat menambah/edit/hapus frequency rules dengan mudah, sehingga sistem dapat disesuaikan dengan perubahan tata tertib.

#### Acceptance Criteria

1. WHEN Operator mengakses halaman "Kelola Frequency Rules", THEN sistem SHALL menampilkan semua jenis pelanggaran dengan rules-nya
2. WHEN Operator menambah rule baru, THEN sistem SHALL memvalidasi threshold tidak overlap dengan rule existing
3. WHEN Operator mengubah rule, THEN sistem SHALL memvalidasi konsistensi threshold
4. WHEN Operator menghapus rule, THEN sistem SHALL meminta konfirmasi dan menampilkan dampak
5. WHEN Operator menyimpan perubahan, THEN sistem SHALL mencatat history perubahan

---

### Requirement 9: Real-time Frequency Display

**User Story:** Sebagai Guru/Wali Kelas, saya ingin melihat frekuensi pelanggaran siswa saat mencatat pelanggaran, sehingga saya tahu apakah threshold akan tercapai.

#### Acceptance Criteria

1. WHEN Guru memilih siswa dan jenis pelanggaran, THEN sistem SHALL menampilkan frekuensi saat ini
2. WHEN frekuensi akan mencapai threshold, THEN sistem SHALL menampilkan warning "Threshold akan tercapai"
3. WHEN threshold akan tercapai, THEN sistem SHALL menampilkan poin yang akan ditambahkan
4. WHEN threshold akan tercapai, THEN sistem SHALL menampilkan sanksi yang akan ditrigger
5. WHEN Guru submit pencatatan, THEN sistem SHALL menampilkan konfirmasi poin dan sanksi yang ditambahkan

---

### Requirement 10: Audit Trail for Point Changes

**User Story:** Sebagai Operator/Kepsek, saya ingin melihat audit trail perubahan poin siswa, sehingga saya dapat tracking kapan dan mengapa poin berubah.

#### Acceptance Criteria

1. WHEN poin siswa berubah, THEN sistem SHALL mencatat perubahan di audit trail
2. WHEN audit trail dicatat, THEN sistem SHALL menyimpan: siswa, jenis pelanggaran, frekuensi, threshold, poin ditambahkan, timestamp
3. WHEN Operator/Kepsek mengakses audit trail, THEN sistem SHALL menampilkan history perubahan poin
4. WHEN audit trail ditampilkan, THEN sistem SHALL menampilkan alasan perubahan (threshold tercapai, manual adjustment, dll)
5. WHEN audit trail ditampilkan, THEN sistem SHALL dapat difilter berdasarkan siswa, tanggal, jenis pelanggaran

---

## Summary

Perubahan ini akan mengubah fundamental logic sistem poin dari **immediate accumulation** menjadi **threshold-based accumulation**, sesuai dengan tata tertib sekolah yang sebenarnya. Perubahan ini juga menambahkan role baru **Waka Sarana** dan memperbaiki sistem surat pemanggilan berdasarkan pembina yang terlibat.

**Dampak:**
- ✅ Akumulasi poin lebih akurat sesuai tata tertib
- ✅ Surat pemanggilan sesuai hierarki pembinaan
- ✅ Pembinaan internal lebih terstruktur
- ✅ Waka Sarana dapat fokus pada pelanggaran fasilitas
- ⚠️ Perubahan besar pada Rules Engine logic
- ⚠️ Memerlukan data migration dan testing ekstensif
