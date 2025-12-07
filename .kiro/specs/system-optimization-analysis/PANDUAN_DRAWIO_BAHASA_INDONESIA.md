# Panduan Membuat Diagram Use Case di draw.io

## Langkah Cepat

### Pilihan 1: Pembuatan Manual (Direkomendasikan)
1. Buka https://app.diagrams.net/
2. Buat Diagram Baru → UML → Diagram Use Case
3. Ikuti struktur di bawah

---

## Struktur Diagram

### Tata Letak:
```
┌────────────────────────────────────────────────────────────────────┐
│                                                                    │
│  [Aktor]          [Batasan Sistem]          [Sistem Eksternal]    │
│   (Kiri)               (Tengah)                   (Kanan)          │
│                                                                    │
└────────────────────────────────────────────────────────────────────┘
```

---

## Langkah Demi Langkah

### LANGKAH 1: Persiapan Kanvas
1. Buka draw.io
2. File → Baru → Diagram Kosong
3. Atur ukuran halaman: A3 Landscape
4. Aktifkan Grid: View → Grid

### LANGKAH 2: Tambahkan Aktor (Sisi Kiri)

**Posisi**: X=50, Y=100, jarak=120px

1. **Operator Sekolah** (Y=100)
   - Bentuk: Aktor (stick figure)
   - Label: "Operator Sekolah"
   - Warna: Biru

2. **Kepala Sekolah** (Y=220)
   - Bentuk: Aktor
   - Label: "Kepala Sekolah"
   - Warna: Merah

3. **Waka Kesiswaan** (Y=340)
   - Bentuk: Aktor
   - Label: "Waka Kesiswaan"
   - Warna: Hijau

4. **Kaprodi** (Y=460)
   - Bentuk: Aktor
   - Label: "Kaprodi"
   - Warna: Oranye

5. **Wali Kelas** (Y=580)
   - Bentuk: Aktor
   - Label: "Wali Kelas"
   - Warna: Ungu

6. **Guru** (Y=700)
   - Bentuk: Aktor
   - Label: "Guru"
   - Warna: Coklat

7. **Wali Murid** (Y=820)
   - Bentuk: Aktor
   - Label: "Wali Murid"
   - Warna: Pink

8. **Waka Sarana** (Y=940)
   - Bentuk: Aktor
   - Label: "Waka Sarana"
   - Warna: Teal

9. **Developer** (Y=1060)
   - Bentuk: Aktor
   - Label: "Developer"
   - Warna: Abu-abu

### LANGKAH 3: Buat Batasan Sistem

**Posisi**: X=200, Y=50, Lebar=800, Tinggi=1100

1. Gambar Persegi Panjang
2. Label: "SIMDIS - Sistem Kedisiplinan"
3. Gaya: Garis putus-putus, isi abu-abu muda
4. Font: Tebal, 16pt

### LANGKAH 4: Tambahkan Use Case (Di Dalam Batasan)

**Format**: Bentuk oval, teks di tengah

#### Modul 1: Autentikasi (Y=100)
```
X=300, Y=100
○ Masuk ke Sistem
○ Keluar dari Sistem
○ Mengelola Profil Pribadi
```

#### Modul 2: Data Induk (Y=250)
```
X=300, Y=250
○ Mengelola Akun Pengguna
○ Mengelola Data Siswa
○ Mengelola Data Jurusan
○ Mengelola Data Kelas
○ Mengelola Jenis Pelanggaran
```

#### Modul 3: Mesin Aturan (Y=450)
```
X=300, Y=450
○ Mengatur Aturan Frekuensi
○ Mengatur Aturan Akumulasi
○ Mengatur Aturan Pembinaan Internal
```

#### Modul 4: Pelanggaran (Y=600)
```
X=600, Y=600
○ Mencatat Pelanggaran Siswa
○ Melihat Riwayat Pelanggaran
○ Mengubah Riwayat Pelanggaran
○ Menghapus Riwayat Pelanggaran
```

#### Modul 5: Tindak Lanjut (Y=800)
```
X=600, Y=800
○ Melihat Daftar Tindak Lanjut
○ Memperbarui Status Tindak Lanjut
○ Menyetujui/Menolak Tindak Lanjut
```

#### Modul 6: Pelaporan (Y=950)
```
X=300, Y=950
○ Melihat Dasbor
○ Membuat Laporan
○ Melihat Siswa Perlu Pembinaan
```

#### Modul 7: Pemberitahuan (Y=1050)
```
X=600, Y=1050
○ Menerima Pemberitahuan
○ Melihat Daftar Pemberitahuan
```

#### Modul 8: Audit (Y=1150)
```
X=300, Y=1150
○ Melihat Log Aktivitas
○ Melihat Login Terakhir
○ Melihat Status Akun
```

### LANGKAH 5: Tambahkan Sistem Eksternal (Sisi Kanan)

**Posisi**: X=1100, Y=400, jarak=150px

1. **Server Email** (Y=400)
   - Bentuk: Aktor (atau Komponen)
   - Label: "<<Eksternal>>\nServer Email"
   - Warna: Biru Muda

2. **Penyimpanan Berkas** (Y=550)
   - Bentuk: Aktor (atau Komponen)
   - Label: "<<Eksternal>>\nPenyimpanan Berkas"
   - Warna: Hijau Muda

3. **Pembuat PDF** (Y=700)
   - Bentuk: Aktor (atau Komponen)
   - Label: "<<Eksternal>>\nPembuat PDF"
   - Warna: Oranye Muda

### LANGKAH 6: Gambar Asosiasi

**Gaya Garis**: Garis solid, tanpa panah

#### Operator Sekolah → Use Case:
- Masuk ke Sistem
- Keluar dari Sistem
- Mengelola Profil Pribadi
- Mengelola Akun Pengguna
- Mengelola Data Siswa
- Mengelola Data Jurusan
- Mengelola Data Kelas
- Mengelola Jenis Pelanggaran
- Mengatur Aturan Frekuensi
- Mengatur Aturan Akumulasi
- Mengatur Aturan Pembinaan Internal
- Mencatat Pelanggaran Siswa
- Melihat Riwayat Pelanggaran
- Mengubah Riwayat Pelanggaran
- Menghapus Riwayat Pelanggaran
- Melihat Daftar Tindak Lanjut
- Melihat Dasbor
- Membuat Laporan
- Melihat Log Aktivitas
- Melihat Login Terakhir
- Melihat Status Akun

#### Kepala Sekolah → Use Case:
- Masuk ke Sistem
- Keluar dari Sistem
- Mengelola Profil Pribadi
- Melihat Riwayat Pelanggaran
- Melihat Daftar Tindak Lanjut
- Menyetujui/Menolak Tindak Lanjut
- Melihat Dasbor
- Membuat Laporan
- Melihat Siswa Perlu Pembinaan
- Menerima Pemberitahuan
- Melihat Daftar Pemberitahuan

#### Waka Kesiswaan → Use Case:
- Masuk ke Sistem
- Keluar dari Sistem
- Mengelola Profil Pribadi
- Mengelola Data Siswa
- Mencatat Pelanggaran Siswa
- Melihat Riwayat Pelanggaran
- Mengubah Riwayat Pelanggaran
- Menghapus Riwayat Pelanggaran
- Melihat Daftar Tindak Lanjut
- Memperbarui Status Tindak Lanjut
- Melihat Dasbor
- Membuat Laporan

#### Kaprodi → Use Case:
- Masuk ke Sistem
- Keluar dari Sistem
- Mengelola Profil Pribadi
- Mencatat Pelanggaran Siswa
- Melihat Riwayat Pelanggaran
- Melihat Daftar Tindak Lanjut
- Memperbarui Status Tindak Lanjut
- Melihat Dasbor

#### Wali Kelas → Use Case:
- Masuk ke Sistem
- Keluar dari Sistem
- Mengelola Profil Pribadi
- Mengelola Data Siswa (Hanya Kontak)
- Mencatat Pelanggaran Siswa
- Melihat Riwayat Pelanggaran
- Melihat Daftar Tindak Lanjut
- Memperbarui Status Tindak Lanjut
- Melihat Dasbor

#### Guru → Use Case:
- Masuk ke Sistem
- Keluar dari Sistem
- Mengelola Profil Pribadi
- Mencatat Pelanggaran Siswa
- Melihat Riwayat Pelanggaran

#### Wali Murid → Use Case:
- Masuk ke Sistem
- Keluar dari Sistem
- Mengelola Profil Pribadi
- Melihat Dasbor (hanya anak sendiri)

#### Waka Sarana → Use Case:
- Masuk ke Sistem
- Keluar dari Sistem
- Mengelola Profil Pribadi
- Mencatat Pelanggaran Siswa
- Melihat Dasbor

#### Developer → Use Case:
- Semua Use Case (gambar garis ke batasan sistem)

### LANGKAH 7: Gambar Hubungan Include

**Gaya Garis**: Panah putus-putus, label "<<include>>"

1. **Mencatat Pelanggaran** --<<include>>--> **Menjalankan Mesin Aturan**
2. **Menjalankan Mesin Aturan** --<<include>>--> **Membuat Tindak Lanjut**
3. **Membuat Tindak Lanjut** --<<include>>--> **Mengirim Pemberitahuan**
4. **Masuk ke Sistem** --<<include>>--> **Memeriksa Status Akun**
5. **Semua CRUD** --<<include>>--> **Mencatat Aktivitas**

### LANGKAH 8: Gambar Hubungan Extend

**Gaya Garis**: Panah putus-putus, label "<<extend>>"

1. **Melihat Pratinjau Dampak** --<<extend>>--> **Mencatat Pelanggaran**
2. **Menyaring berdasarkan Kriteria** --<<extend>>--> **Melihat Riwayat**
3. **Menyaring berdasarkan Kriteria** --<<extend>>--> **Melihat Tindak Lanjut**

### LANGKAH 9: Hubungkan Sistem Eksternal

**Gaya Garis**: Garis putus-putus

1. **Mengirim Pemberitahuan** -----> **Server Email**
2. **Mengunggah Bukti Foto** -----> **Penyimpanan Berkas**
3. **Membuat Laporan** -----> **Pembuat PDF**

### LANGKAH 10: Tambahkan Label Modul

Tambahkan kotak teks untuk mengelompokkan use case:

1. **"Autentikasi & Otorisasi"** (di atas grup Masuk)
2. **"Pengelolaan Data Induk"** (di atas grup Mengelola Pengguna)
3. **"Pengaturan Mesin Aturan"** (di atas grup Mengatur Aturan)
4. **"Pengelolaan Pelanggaran"** (di atas grup Mencatat Pelanggaran)
5. **"Pengelolaan Tindak Lanjut"** (di atas grup Melihat Tindak Lanjut)
6. **"Pelaporan & Pemantauan"** (di atas grup Melihat Dasbor)
7. **"Sistem Pemberitahuan"** (di atas grup Menerima Pemberitahuan)
8. **"Audit & Log Aktivitas"** (di atas grup Melihat Log)

### LANGKAH 11: Tambahkan Legenda

**Posisi**: Pojok kanan bawah

```
┌─────────────────────────┐
│        LEGENDA          │
├─────────────────────────┤
│ ○ Use Case              │
│ ─── Asosiasi            │
│ ---> <<include>>        │
│ ---> <<extend>>         │
│ 👤 Aktor                │
│ ⬜ Batasan Sistem       │
└─────────────────────────┘
```

### LANGKAH 12: Tambahkan Judul & Metadata

**Posisi**: Tengah atas

```
┌─────────────────────────────────────────────┐
│  SIMDIS - Diagram Use Case                  │
│  Sistem Kedisiplinan SMKN 1 Siak            │
│                                             │
│  Versi: 1.0                                 │
│  Tanggal: Desember 2025                     │
│  Pembuat: Tim Pengembang                    │
└─────────────────────────────────────────────┘
```

---

## Skema Warna

### Aktor:
- **Operator Sekolah**: #0066CC (Biru)
- **Kepala Sekolah**: #CC0000 (Merah)
- **Waka Kesiswaan**: #00CC66 (Hijau)
- **Kaprodi**: #FF9900 (Oranye)
- **Wali Kelas**: #9933CC (Ungu)
- **Guru**: #996633 (Coklat)
- **Wali Murid**: #FF66CC (Pink)
- **Waka Sarana**: #00CCCC (Teal)
- **Developer**: #666666 (Abu-abu)

### Use Case:
- **Autentikasi**: #E6F2FF (Biru Muda)
- **Data Induk**: #E6FFE6 (Hijau Muda)
- **Mesin Aturan**: #FFF2E6 (Oranye Muda)
- **Pelanggaran**: #FFE6E6 (Merah Muda)
- **Tindak Lanjut**: #F2E6FF (Ungu Muda)
- **Pelaporan**: #FFFFE6 (Kuning Muda)
- **Pemberitahuan**: #E6FFFF (Cyan Muda)
- **Audit**: #F2F2F2 (Abu-abu Muda)

### Batasan Sistem:
- Garis: #333333 (Abu-abu Gelap), Putus-putus
- Isi: #F9F9F9 (Abu-abu Sangat Muda)

---

## Tips untuk Diagram yang Lebih Baik

### 1. Perataan:
- Gunakan alat perataan draw.io
- Arrange → Align → Distribute Horizontally/Vertically

### 2. Jarak:
- Jaga jarak konsisten antar elemen
- Gunakan snap to grid (View → Grid → Snap to Grid)

### 3. Pengelompokan:
- Kelompokkan use case terkait
- Gunakan kontainer atau latar belakang berwarna

### 4. Label:
- Gunakan label yang jelas dan ringkas
- Hindari singkatan
- Gunakan penamaan konsisten

### 5. Garis:
- Hindari garis bersilangan jika memungkinkan
- Gunakan waypoint untuk merutekan garis dengan rapi
- Jaga garis selurus mungkin

### 6. Ukuran:
- Jaga ukuran oval use case konsisten
- Aktor harus berukuran sama
- Teks use case harus pas dengan nyaman

---

## Pilihan Ekspor

### Untuk Dokumentasi:
- **PNG**: Resolusi tinggi (300 DPI)
- **PDF**: Format vektor, dapat diperbesar
- **SVG**: Ramah web, dapat diperbesar

### Untuk Presentasi:
- **PNG**: Latar belakang transparan
- **PDF**: Siap cetak

### Untuk Kolaborasi:
- **draw.io XML**: Format dapat diedit
- **Tautan Berbagi**: Kolaborasi online

---

## Daftar Periksa Validasi

Sebelum finalisasi:

- [ ] Semua aktor terhubung ke use case yang relevan
- [ ] Semua use case berada di dalam batasan sistem
- [ ] Hubungan Include/Extend benar
- [ ] Sistem eksternal terhubung dengan benar
- [ ] Label jelas dan dapat dibaca
- [ ] Warna konsisten
- [ ] Tata letak seimbang
- [ ] Tidak ada elemen yang tumpang tindih
- [ ] Legenda ada
- [ ] Judul dan metadata ditambahkan

---

## Alternatif: Versi Sederhana

Jika diagram terlalu kompleks, buat beberapa diagram:

### Diagram 1: Gambaran Umum Tingkat Tinggi
- Tampilkan hanya modul utama
- Kelompokkan use case ke dalam paket
- Tampilkan hanya aktor utama

### Diagram 2: Autentikasi & Data Induk
- Detail alur autentikasi
- Detail CRUD data induk

### Diagram 3: Pelanggaran & Tindak Lanjut
- Detail alur kerja pelanggaran
- Detail proses persetujuan

### Diagram 4: Pelaporan & Pemantauan
- Detail fitur pelaporan
- Detail tampilan dasbor

---

## Perkiraan Waktu

- **Versi Sederhana** (use case utama saja): 30 menit
- **Versi Lengkap** (semua detail): 2-3 jam
- **Versi Profesional** (dengan styling): 4-5 jam

---

## Sumber Daya

- **draw.io**: https://app.diagrams.net/
- **Tutorial UML**: https://www.visual-paradigm.com/guide/uml-unified-modeling-language/what-is-use-case-diagram/
- **Praktik Terbaik**: https://creately.com/blog/diagrams/use-case-diagram-tutorial/

---

## Ringkasan

Panduan ini menyediakan:
1. ✅ Daftar use case lengkap (28 use case)
2. ✅ Deskripsi aktor detail (9 aktor)
3. ✅ Panduan pembuatan langkah demi langkah
4. ✅ Tata letak dan posisi
5. ✅ Skema warna
6. ✅ Hubungan (include, extend, asosiasi)
7. ✅ Tips dan praktik terbaik

Anda tinggal mengikuti panduan ini di draw.io untuk membuat diagram yang profesional! 🎨
