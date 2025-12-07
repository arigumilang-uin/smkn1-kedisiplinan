# Diagram Use Case - Sistem Kedisiplinan SMKN 1 Siak

## Gambaran Umum Sistem
**Nama Sistem**: SIMDIS (Sistem Informasi Manajemen Disiplin Siswa)  
**Tujuan**: Mengelola pencatatan pelanggaran siswa, tindak lanjut pembinaan, dan persetujuan kepala sekolah secara otomatis berbasis aturan

---

## Daftar Aktor

### 1. Operator Sekolah
Administrator sistem dengan akses penuh untuk mengelola data induk dan pengaturan sistem.

### 2. Kepala Sekolah
Pejabat yang menyetujui/menolak tindak lanjut dan memantau siswa bermasalah.

### 3. Wakil Kepala Sekolah Bidang Kesiswaan (Waka Kesiswaan)
Pengelola kesiswaan yang memantau dan mengelola pelanggaran siswa.

### 4. Kepala Program Studi (Kaprodi)
Pengelola jurusan yang memantau siswa di jurusannya.

### 5. Wali Kelas
Guru yang bertanggung jawab atas siswa di kelasnya.

### 6. Guru
Pendidik yang mencatat pelanggaran siswa.

### 7. Wali Murid
Orang tua/wali siswa yang memantau pelanggaran anaknya.

### 8. Wakil Kepala Sekolah Bidang Sarana (Waka Sarana)
Pengelola sarana prasarana dengan akses terbatas.

### 9. Developer
Pengembang sistem dengan akses penuh untuk pemeliharaan.

---

## Daftar Use Case

### MODUL 1: AUTENTIKASI & OTORISASI

#### UC-01: Masuk ke Sistem
**Aktor**: Semua Pengguna  
**Deskripsi**: Pengguna masuk ke sistem menggunakan nama pengguna atau email dan kata sandi  
**Alur Utama**:
1. Pengguna membuka halaman masuk
2. Pengguna memasukkan nama pengguna/email dan kata sandi
3. Sistem memvalidasi kredensial
4. Sistem memeriksa status akun (aktif/nonaktif)
5. Sistem mencatat waktu masuk terakhir
6. Sistem mengarahkan pengguna ke halaman sesuai peran

**Alur Alternatif**:
- 3a. Kredensial salah → Sistem menampilkan pesan kesalahan
- 4a. Akun nonaktif → Sistem menolak akses dan menampilkan pesan

#### UC-02: Keluar dari Sistem
**Aktor**: Semua Pengguna  
**Deskripsi**: Pengguna keluar dari sistem  
**Alur Utama**:
1. Pengguna menekan tombol keluar
2. Sistem menghapus sesi pengguna
3. Sistem mengarahkan ke halaman masuk

#### UC-03: Mengelola Profil Pribadi
**Aktor**: Semua Pengguna  
**Deskripsi**: Pengguna memperbarui informasi profil pribadi  
**Alur Utama**:
1. Pengguna membuka halaman profil
2. Pengguna mengubah informasi (nama, email, nama pengguna, kata sandi, foto)
3. Sistem memvalidasi perubahan
4. Sistem menyimpan perubahan
5. Sistem mencatat aktivitas perubahan

---

### MODUL 2: PENGELOLAAN DATA INDUK

#### UC-04: Mengelola Akun Pengguna
**Aktor**: Operator Sekolah  
**Deskripsi**: Operator mengelola akun pengguna sistem (tambah, ubah, hapus, aktifkan/nonaktifkan)  
**Alur Utama**:
1. Operator membuka halaman pengelolaan pengguna
2. Operator melakukan aksi (tambah/ubah/hapus/toggle status)
3. Sistem memvalidasi data
4. Sistem menyimpan perubahan
5. Sistem mencatat aktivitas

**Sub Use Case**:
- Menambah Pengguna Baru
- Mengubah Data Pengguna
- Menghapus Pengguna
- Mengaktifkan/Menonaktifkan Akun
- Menetapkan Peran Pengguna
- Menetapkan Wali Kelas
- Menetapkan Kepala Program Studi

#### UC-05: Mengelola Data Siswa
**Aktor**: Operator Sekolah, Waka Kesiswaan  
**Deskripsi**: Mengelola data siswa (tambah, ubah, hapus)  
**Alur Utama**:
1. Pengguna membuka halaman pengelolaan siswa
2. Pengguna melakukan aksi (tambah/ubah/hapus)
3. Sistem memvalidasi data
4. Sistem membuat akun wali murid otomatis (opsional)
5. Sistem menyimpan perubahan
6. Sistem mencatat aktivitas

**Sub Use Case**:
- Menambah Siswa Tunggal
- Menambah Siswa Massal (Bulk)
- Mengubah Data Siswa
- Menghapus Data Siswa
- Menghubungkan Akun Wali Murid
- Memperbarui Kontak Wali Murid (Wali Kelas)

#### UC-06: Mengelola Data Jurusan
**Aktor**: Operator Sekolah  
**Deskripsi**: Mengelola data jurusan/program studi  
**Alur Utama**:
1. Operator membuka halaman pengelolaan jurusan
2. Operator melakukan aksi (tambah/ubah/hapus)
3. Sistem memvalidasi data
4. Sistem menyimpan perubahan
5. Sistem mencatat aktivitas

#### UC-07: Mengelola Data Kelas
**Aktor**: Operator Sekolah  
**Deskripsi**: Mengelola data kelas dan penugasan wali kelas  
**Alur Utama**:
1. Operator membuka halaman pengelolaan kelas
2. Operator melakukan aksi (tambah/ubah/hapus)
3. Operator menetapkan wali kelas
4. Sistem memvalidasi data
5. Sistem menyimpan perubahan
6. Sistem mencatat aktivitas

#### UC-08: Mengelola Jenis Pelanggaran
**Aktor**: Operator Sekolah  
**Deskripsi**: Mengelola jenis pelanggaran dan kategorinya  
**Alur Utama**:
1. Operator membuka halaman pengelolaan jenis pelanggaran
2. Operator melakukan aksi (tambah/ubah/hapus/aktifkan)
3. Sistem memeriksa apakah jenis pelanggaran sudah digunakan (untuk hapus)
4. Sistem memvalidasi data
5. Sistem menyimpan perubahan
6. Sistem mencatat aktivitas

**Sub Use Case**:
- Menambah Jenis Pelanggaran
- Mengubah Jenis Pelanggaran
- Menghapus Jenis Pelanggaran (jika belum digunakan)
- Mengaktifkan/Menonaktifkan Jenis Pelanggaran

---

### MODUL 3: PENGATURAN MESIN ATURAN

#### UC-09: Mengatur Aturan Frekuensi
**Aktor**: Operator Sekolah, Waka Kesiswaan  
**Deskripsi**: Mengatur aturan berbasis frekuensi pelanggaran (X kali dalam Y hari → Surat Z)  
**Alur Utama**:
1. Operator memilih jenis pelanggaran
2. Operator menambah aturan frekuensi
3. Operator mengisi: jumlah pelanggaran, rentang hari, tipe surat
4. Sistem memvalidasi aturan
5. Sistem mengaktifkan jenis pelanggaran otomatis
6. Sistem menyimpan aturan
7. Sistem mencatat aktivitas

**Sub Use Case**:
- Menambah Aturan Frekuensi
- Mengubah Aturan Frekuensi
- Menghapus Aturan Frekuensi
- Melihat Pratinjau Dampak Aturan

#### UC-10: Mengatur Aturan Akumulasi
**Aktor**: Operator Sekolah, Waka Kesiswaan  
**Deskripsi**: Mengatur ambang batas poin untuk setiap tipe surat  
**Alur Utama**:
1. Operator membuka halaman pengaturan mesin aturan
2. Operator mengatur ambang batas poin untuk:
   - Surat Panggilan 1
   - Surat Panggilan 2
   - Surat Panggilan 3
   - Surat Panggilan 4
3. Sistem memvalidasi ambang batas
4. Sistem menyimpan pengaturan
5. Sistem mencatat aktivitas

**Sub Use Case**:
- Mengatur Ambang Batas Surat 1
- Mengatur Ambang Batas Surat 2
- Mengatur Ambang Batas Surat 3
- Mengatur Ambang Batas Surat 4
- Melihat Pratinjau Dampak Perubahan

#### UC-11: Mengatur Aturan Pembinaan Internal
**Aktor**: Operator Sekolah, Waka Kesiswaan  
**Deskripsi**: Mengatur aturan pembinaan internal (jenis pelanggaran + pembina tambahan)  
**Alur Utama**:
1. Operator membuka halaman aturan pembinaan internal
2. Operator menambah aturan (jenis pelanggaran + pembina tambahan)
3. Sistem memvalidasi aturan
4. Sistem menyimpan aturan
5. Sistem mencatat aktivitas

**Sub Use Case**:
- Menambah Aturan Pembinaan Internal
- Mengubah Aturan Pembinaan Internal
- Menghapus Aturan Pembinaan Internal

---

### MODUL 4: PENGELOLAAN PELANGGARAN

#### UC-12: Mencatat Pelanggaran Siswa
**Aktor**: Guru, Wali Kelas, Waka Kesiswaan, Kaprodi, Waka Sarana  
**Deskripsi**: Mencatat pelanggaran yang dilakukan siswa (dapat memilih banyak siswa dan jenis pelanggaran sekaligus)  
**Alur Utama**:
1. Pengguna membuka formulir pencatatan pelanggaran
2. Pengguna memilih siswa (dapat lebih dari satu)
3. Pengguna memilih jenis pelanggaran (dapat lebih dari satu)
4. Pengguna mengisi tanggal, waktu, mengunggah bukti foto, dan keterangan
5. Pengguna menekan tombol pratinjau (opsional)
6. Sistem menampilkan pratinjau dampak pencatatan
7. Pengguna mengonfirmasi dan menyimpan
8. Sistem menyimpan riwayat pelanggaran
9. Sistem menjalankan mesin aturan otomatis
10. Sistem membuat/memperbarui tindak lanjut
11. Sistem mengirim pemberitahuan (jika diperlukan)
12. Sistem mencatat aktivitas

**Sub Use Case**:
- Memilih Siswa (dengan filter)
- Memilih Jenis Pelanggaran (dengan filter)
- Mengunggah Bukti Foto
- Melihat Pratinjau Dampak
- Menjalankan Mesin Aturan
- Mengirim Pemberitahuan

**Ekstensi**:
- Menyaring Siswa berdasarkan Tingkat/Jurusan/Kelas
- Menyaring Pelanggaran berdasarkan Kategori
- Mencari Siswa
- Mencari Pelanggaran

#### UC-13: Melihat Riwayat Pelanggaran
**Aktor**: Operator Sekolah, Waka Kesiswaan, Wali Kelas, Kaprodi, Kepala Sekolah  
**Deskripsi**: Melihat daftar riwayat pelanggaran dengan berbagai filter  
**Alur Utama**:
1. Pengguna membuka halaman riwayat pelanggaran
2. Pengguna menerapkan filter (siswa, tanggal, jenis, pencatat)
3. Sistem menampilkan hasil yang disaring
4. Pengguna dapat melihat detail
5. Pengguna dapat mengubah/menghapus (jika berwenang)

**Varian**:
- **Riwayat Semua Siswa**: Operator, Waka Kesiswaan, Kepala Sekolah (akses penuh)
- **Riwayat Saya**: Guru, Wali Kelas, Kaprodi, Waka Sarana (hanya yang dicatat sendiri)

**Sub Use Case**:
- Menyaring berdasarkan Siswa
- Menyaring berdasarkan Tanggal
- Menyaring berdasarkan Jenis Pelanggaran
- Menyaring berdasarkan Pencatat
- Melihat Detail Pelanggaran
- Mengubah Riwayat (Operator atau Pencatat)
- Menghapus Riwayat (Operator atau Pencatat)

#### UC-14: Mengubah Riwayat Pelanggaran
**Aktor**: Operator Sekolah, Pencatat (pemilik)  
**Deskripsi**: Mengubah data riwayat pelanggaran yang sudah dicatat  
**Alur Utama**:
1. Pengguna membuka detail riwayat
2. Pengguna menekan tombol ubah
3. Sistem memeriksa kewenangan
4. Pengguna memperbarui data
5. Sistem memvalidasi perubahan
6. Sistem menjalankan ulang mesin aturan (jika diperlukan)
7. Sistem menyimpan perubahan
8. Sistem mencatat aktivitas

#### UC-15: Menghapus Riwayat Pelanggaran
**Aktor**: Operator Sekolah, Pencatat (pemilik)  
**Deskripsi**: Menghapus riwayat pelanggaran  
**Alur Utama**:
1. Pengguna membuka detail riwayat
2. Pengguna menekan tombol hapus
3. Sistem memeriksa kewenangan
4. Sistem meminta konfirmasi penghapusan
5. Sistem menghapus riwayat
6. Sistem menghitung ulang tindak lanjut
7. Sistem mencatat aktivitas

---

### MODUL 5: PENGELOLAAN TINDAK LANJUT

#### UC-16: Melihat Daftar Tindak Lanjut
**Aktor**: Wali Kelas, Waka Kesiswaan, Kepala Sekolah, Operator Sekolah, Kaprodi  
**Deskripsi**: Melihat daftar tindak lanjut pembinaan dengan filter  
**Alur Utama**:
1. Pengguna membuka halaman tindak lanjut
2. Pengguna menerapkan filter (siswa, status, surat)
3. Sistem menampilkan hasil yang disaring
4. Pengguna dapat melihat detail

**Sub Use Case**:
- Menyaring berdasarkan Siswa
- Menyaring berdasarkan Status
- Menyaring berdasarkan Tipe Surat
- Melihat Detail Tindak Lanjut

#### UC-17: Memperbarui Status Tindak Lanjut
**Aktor**: Wali Kelas, Waka Kesiswaan, Kepala Sekolah, Operator Sekolah, Kaprodi  
**Deskripsi**: Memperbarui status tindak lanjut pembinaan  
**Alur Utama**:
1. Pengguna membuka detail tindak lanjut
2. Pengguna memilih status baru
3. Pengguna mengisi catatan
4. Sistem memvalidasi transisi status
5. Sistem menyimpan perubahan
6. Sistem mencatat aktivitas

**Sub Use Case**:
- Mengubah ke Status "Dalam Proses"
- Mengubah ke Status "Selesai"
- Menambah Catatan Pembinaan

**Aturan Bisnis**:
- Tidak dapat menurunkan status
- Tidak dapat mengubah status "Disetujui" (hanya Kepala Sekolah)
- Tidak dapat mengubah status "Menunggu Persetujuan" (hanya Kepala Sekolah)

#### UC-18: Menyetujui/Menolak Tindak Lanjut
**Aktor**: Kepala Sekolah  
**Deskripsi**: Menyetujui atau menolak tindak lanjut yang memerlukan persetujuan  
**Alur Utama**:
1. Kepala Sekolah menerima pemberitahuan
2. Kepala Sekolah membuka detail tindak lanjut
3. Kepala Sekolah meninjau kasus
4. Kepala Sekolah menyetujui atau menolak
5. Sistem memperbarui status
6. Sistem mengirim pemberitahuan ke pencatat
7. Sistem mencatat aktivitas

**Sub Use Case**:
- Menyetujui (status → "Disetujui")
- Menolak (status → "Ditolak")
- Menambah Catatan Persetujuan

---

### MODUL 6: PELAPORAN & PEMANTAUAN

#### UC-19: Melihat Dasbor
**Aktor**: Semua Pengguna  
**Deskripsi**: Melihat dasbor sesuai peran pengguna  
**Alur Utama**:
1. Pengguna masuk ke sistem
2. Sistem mengarahkan ke dasbor sesuai peran
3. Sistem menampilkan statistik dan grafik
4. Pengguna dapat melihat detail lebih lanjut

**Varian**:
- **Dasbor Operator**: Total siswa, pelanggaran, tindak lanjut, grafik
- **Dasbor Kepala Sekolah**: Persetujuan tertunda, siswa perlu pembinaan, statistik
- **Dasbor Kaprodi**: Statistik jurusan, siswa jurusan
- **Dasbor Wali Kelas**: Statistik kelas, siswa kelas
- **Dasbor Wali Murid**: Pelanggaran anak, tindak lanjut anak

#### UC-20: Membuat Laporan
**Aktor**: Kepala Sekolah, Waka Kesiswaan, Operator  
**Deskripsi**: Membuat laporan pelanggaran dalam format PDF  
**Alur Utama**:
1. Pengguna membuka halaman laporan
2. Pengguna memilih jenis laporan
3. Pengguna mengatur rentang tanggal
4. Pengguna menerapkan filter
5. Sistem membuat laporan
6. Pengguna melihat pratinjau laporan
7. Pengguna mengunduh PDF

**Sub Use Case**:
- Memilih Jenis Laporan
- Mengatur Rentang Tanggal
- Menerapkan Filter
- Melihat Pratinjau Laporan
- Mengunduh PDF

#### UC-21: Melihat Siswa Perlu Pembinaan
**Aktor**: Kepala Sekolah  
**Deskripsi**: Melihat daftar siswa yang memerlukan pembinaan khusus  
**Alur Utama**:
1. Kepala Sekolah membuka halaman siswa perlu pembinaan
2. Sistem menampilkan siswa dengan kasus aktif
3. Sistem menampilkan siswa dengan poin tinggi
4. Kepala Sekolah dapat melihat detail
5. Kepala Sekolah dapat mengambil tindakan

**Sub Use Case**:
- Menyaring berdasarkan Jurusan
- Menyaring berdasarkan Kelas
- Melihat Detail Siswa
- Melihat Riwayat Pelanggaran

---

### MODUL 7: SISTEM PEMBERITAHUAN

#### UC-22: Menerima Pemberitahuan
**Aktor**: Kepala Sekolah  
**Deskripsi**: Menerima pemberitahuan untuk persetujuan tindak lanjut  
**Alur Utama**:
1. Sistem mendeteksi kasus memerlukan persetujuan
2. Sistem mengirim pemberitahuan email
3. Sistem membuat pemberitahuan dalam aplikasi
4. Kepala Sekolah melihat lencana pemberitahuan
5. Kepala Sekolah menekan pemberitahuan
6. Sistem mengarahkan ke detail tindak lanjut

**Sub Use Case**:
- Pemberitahuan Email
- Pemberitahuan Dalam Aplikasi
- Penghitung Lencana Pemberitahuan

#### UC-23: Melihat Daftar Pemberitahuan
**Aktor**: Kepala Sekolah  
**Deskripsi**: Melihat daftar semua pemberitahuan  
**Alur Utama**:
1. Kepala Sekolah menekan ikon lonceng pemberitahuan
2. Sistem menampilkan daftar pemberitahuan
3. Kepala Sekolah dapat menandai sebagai sudah dibaca
4. Kepala Sekolah dapat menekan untuk melihat detail

---

### MODUL 8: AUDIT & LOG AKTIVITAS

#### UC-24: Melihat Log Aktivitas
**Aktor**: Operator Sekolah  
**Deskripsi**: Melihat catatan aktivitas sistem  
**Alur Utama**:
1. Operator membuka halaman audit & log
2. Operator memilih tab (Aktivitas, Login Terakhir, Status Akun)
3. Operator menerapkan filter
4. Sistem menampilkan log yang disaring
5. Operator dapat melihat detail
6. Operator dapat mengekspor CSV

**Sub Use Case**:
- Menyaring berdasarkan Jenis
- Menyaring berdasarkan Pengguna
- Menyaring berdasarkan Rentang Tanggal
- Mencari Deskripsi
- Melihat Detail
- Mengekspor CSV

#### UC-25: Melihat Login Terakhir
**Aktor**: Operator Sekolah  
**Deskripsi**: Melihat waktu login terakhir semua pengguna  
**Alur Utama**:
1. Operator membuka audit & log → tab Login Terakhir
2. Operator menerapkan filter (peran, pencarian)
3. Sistem menampilkan daftar pengguna dengan waktu login terakhir
4. Operator dapat melihat waktu relatif dan stempel waktu lengkap

#### UC-26: Melihat Status Akun
**Aktor**: Operator Sekolah  
**Deskripsi**: Melihat status akun pengguna (aktif/nonaktif)  
**Alur Utama**:
1. Operator membuka audit & log → tab Status Akun
2. Operator menerapkan filter (status, peran, pencarian)
3. Sistem menampilkan daftar pengguna dengan status
4. Operator dapat mengaktifkan/menonaktifkan akun

---

### MODUL 9: PENGELOLAAN BERKAS

#### UC-27: Melihat Bukti Foto
**Aktor**: Semua Pengguna (kecuali Wali Murid)  
**Deskripsi**: Melihat bukti foto pelanggaran  
**Alur Utama**:
1. Pengguna membuka detail riwayat
2. Pengguna menekan tombol lihat foto
3. Sistem menampilkan foto dalam modal/tab baru
4. Pengguna dapat mengunduh foto

#### UC-28: Mengunduh Surat Panggilan
**Aktor**: Wali Kelas, Waka Kesiswaan, Kepala Sekolah, Operator Sekolah, Kaprodi  
**Deskripsi**: Mengunduh surat panggilan dalam format PDF  
**Alur Utama**:
1. Pengguna membuka detail tindak lanjut
2. Pengguna menekan tombol unduh surat
3. Sistem membuat PDF
4. Sistem mengunduh PDF ke perangkat pengguna

---

### MODUL 10: FITUR KHUSUS

#### UC-29: Melihat Riwayat Saya
**Aktor**: Guru, Wali Kelas, Waka Kesiswaan, Kaprodi, Waka Sarana, Operator Sekolah  
**Deskripsi**: Melihat riwayat pelanggaran yang dicatat oleh pengguna sendiri  
**Alur Utama**:
1. Pengguna membuka halaman "Riwayat Saya"
2. Sistem menampilkan hanya pelanggaran yang dicatat oleh pengguna
3. Pengguna dapat menyaring berdasarkan tanggal, siswa, jenis
4. Pengguna dapat mengubah atau menghapus riwayat sendiri

**Sub Use Case**:
- Menyaring Riwayat Sendiri
- Mengubah Riwayat Sendiri
- Menghapus Riwayat Sendiri

#### UC-30: Melihat Siswa Perlu Pembinaan (Kepala Sekolah)
**Aktor**: Kepala Sekolah  
**Deskripsi**: Melihat daftar siswa yang memerlukan perhatian khusus  
**Alur Utama**:
1. Kepala Sekolah membuka halaman siswa perlu pembinaan
2. Sistem menampilkan siswa dengan:
   - Kasus aktif yang memerlukan persetujuan
   - Poin pelanggaran tinggi
   - Pelanggaran berulang
3. Kepala Sekolah dapat menyaring berdasarkan jurusan/kelas
4. Kepala Sekolah dapat melihat detail dan mengambil tindakan

**Sub Use Case**:
- Menyaring berdasarkan Jurusan
- Menyaring berdasarkan Kelas
- Melihat Detail Siswa
- Melihat Riwayat Lengkap

#### UC-31: Mengelola Profil Lengkap
**Aktor**: Semua Pengguna (pertama kali login)  
**Deskripsi**: Melengkapi profil wajib setelah login pertama kali  
**Alur Utama**:
1. Pengguna login pertama kali
2. Sistem mendeteksi profil belum lengkap
3. Sistem mengarahkan ke halaman lengkapi profil
4. Pengguna mengisi data wajib (nama lengkap, kontak, dll)
5. Sistem memvalidasi data
6. Sistem menyimpan dan menandai profil sebagai lengkap
7. Sistem mengarahkan ke dashboard

**Aturan Bisnis**:
- Wajib dilakukan sebelum akses fitur lain
- Hanya sekali saat pertama login
- Tidak bisa dilewati

---

## Hubungan Antar Use Case

### Hubungan Include (Wajib):
- **UC-12 (Mencatat Pelanggaran)** include **Menjalankan Mesin Aturan**
- **Menjalankan Mesin Aturan** include **Membuat Tindak Lanjut**
- **Membuat Tindak Lanjut** include **Mengirim Pemberitahuan** (jika Kepala Sekolah terlibat)
- **UC-01 (Masuk ke Sistem)** include **Memeriksa Status Akun**
- **Semua Operasi CRUD** include **Mencatat Aktivitas**

### Hubungan Extend (Opsional):
- **Melihat Pratinjau Dampak** extend **UC-12 (Mencatat Pelanggaran)**
- **Menyaring berdasarkan Kriteria** extend **UC-13 (Melihat Riwayat)**
- **Menyaring berdasarkan Kriteria** extend **UC-16 (Melihat Tindak Lanjut)**

### Generalisasi:
- **Mengelola Data Induk** menggeneralisasi:
  - UC-04 (Mengelola Akun Pengguna)
  - UC-05 (Mengelola Data Siswa)
  - UC-06 (Mengelola Data Jurusan)
  - UC-07 (Mengelola Data Kelas)
  - UC-08 (Mengelola Jenis Pelanggaran)

---

## Batasan Sistem

### Di Dalam Sistem:
- Autentikasi & Otorisasi
- Pengelolaan Data Induk
- Mesin Aturan
- Pencatatan Pelanggaran
- Pengelolaan Tindak Lanjut
- Pelaporan
- Pemberitahuan
- Log Audit

### Di Luar Sistem (Eksternal):
- Server Email (untuk pemberitahuan)
- Penyimpanan Berkas (untuk foto)
- Pembuat PDF (untuk laporan)

---

## Prioritas Use Case

### Prioritas Tinggi (Fitur Inti):
1. UC-01: Masuk ke Sistem
2. UC-02: Keluar dari Sistem
3. UC-12: Mencatat Pelanggaran Siswa
4. UC-13: Melihat Riwayat Pelanggaran
5. Menjalankan Mesin Aturan (otomatis)
6. UC-18: Menyetujui/Menolak Tindak Lanjut
7. UC-19: Melihat Dasbor

### Prioritas Sedang:
1. UC-04 s/d UC-08: Mengelola Data Induk
2. UC-09 s/d UC-11: Mengatur Mesin Aturan
3. UC-20: Membuat Laporan
4. UC-22 s/d UC-23: Sistem Pemberitahuan
5. UC-17: Memperbarui Status Tindak Lanjut

### Prioritas Rendah:
1. UC-24 s/d UC-26: Audit & Log Aktivitas
2. UC-03: Mengelola Profil Pribadi
3. UC-27 s/d UC-28: Pengelolaan Berkas
4. Alat Developer

---

## Ringkasan

**Total Use Case**: 31 Use Case Utama  
**Total Aktor**: 9 Aktor  
**Total Modul**: 10 Modul  
**Kompleksitas**: Sedang-Tinggi  

**Fitur Utama**:
- Kontrol akses berbasis peran (9 peran berbeda)
- Otomasi mesin aturan (frekuensi + akumulasi + pembinaan internal)
- Alur kerja persetujuan (Kepala Sekolah)
- Sistem pemberitahuan (email + in-app)
- Pelaporan komprehensif (PDF)
- Jejak audit lengkap
- Preview dampak sebelum submit
- Pencatatan multi-select (siswa & pelanggaran)

**Jenis Sistem**: Sistem Informasi Manajemen Berbasis Web  
**Arsitektur**: MVC dengan Lapisan Layanan  
**Framework**: Laravel 12

---

## Verifikasi dengan Sistem Aktual

### ✅ Verified Features:
1. **Authentication**: Login, Logout, Profile Management
2. **Dashboards**: 6 role-specific dashboards
3. **Master Data**: Users, Siswa (single + bulk), Jurusan, Kelas, Jenis Pelanggaran
4. **Rules Engine**: Frequency Rules, Accumulation Rules, Pembinaan Internal Rules
5. **Pelanggaran**: Catat (multi-select), Preview, View, Edit, Delete
6. **Tindak Lanjut**: View, Update Status, Approve/Reject, Download Surat
7. **Reporting**: Dashboard statistics, PDF reports
8. **Notification**: Email + In-app for Kepala Sekolah
9. **Audit**: Activity Log, Last Login, Account Status
10. **Special Features**: Riwayat Saya, Siswa Perlu Pembinaan, Profile Completion

### ✅ Verified Actors & Permissions:
- **Operator Sekolah**: Full access to all features
- **Kepala Sekolah**: Approval, monitoring, reporting
- **Waka Kesiswaan**: Management + rules configuration
- **Kaprodi**: Jurusan-scoped access
- **Wali Kelas**: Kelas-scoped access + contact update
- **Guru**: Basic recording + view own records
- **Wali Murid**: View own child only
- **Waka Sarana**: Limited recording access
- **Developer**: Full system access + impersonation

### ✅ Verified Routes:
- Total routes verified: 50+ routes
- All use cases mapped to actual routes
- All permissions verified from middleware
