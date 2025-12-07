# Extended Access for Waka Kesiswaan & Kepala Sekolah

## Overview
Implementasi akses tambahan untuk role Waka Kesiswaan dan Kepala Sekolah untuk melihat data jurusan dan kelas dengan grafik pelanggaran, serta akses ke manajemen rules untuk Waka Kesiswaan.

## Changes Implemented

### 1. Routes (routes/web.php)
**Frequency Rules & Pembinaan Internal:**
- Updated middleware: `role:Operator Sekolah,Waka Kesiswaan`
- Waka Kesiswaan dapat manage rules seperti Operator

**Data Jurusan & Kelas (Read-only):**
- New routes group: `role:Waka Kesiswaan,Kepala Sekolah`
- Routes:
  - `data-jurusan.index` - List jurusan with statistics
  - `data-jurusan.show` - Detail jurusan with charts
  - `data-kelas.index` - List kelas with statistics
  - `data-kelas.show` - Detail kelas with charts

### 2. Controllers

#### DataJurusanController (app/Http/Controllers/DataJurusanController.php)
**index():**
- List all jurusan with statistics:
  - Total siswa
  - Total kelas
  - Total pelanggaran
  - Pelanggaran bulan ini

**show($jurusan):**
- Detail jurusan with:
  - Statistics cards
  - Chart: Pelanggaran per bulan (last 6 months) - Line chart
  - Chart: Pelanggaran per kategori - Doughnut chart
  - Top 10 siswa with most violations
  - Link to siswa profile

#### DataKelasController (app/Http/Controllers/DataKelasController.php)
**index():**
- List all kelas with statistics:
  - Total siswa
  - Total pelanggaran
  - Pelanggaran bulan ini

**show($kelas):**
- Detail kelas with:
  - Statistics cards
  - Chart: Pelanggaran per bulan (last 6 months) - Line chart
  - Chart: Pelanggaran per kategori - Doughnut chart
  - Top 10 siswa with most violations
  - Link to siswa profile

### 3. Views

#### Data Jurusan Views
**resources/views/data_jurusan/index.blade.php:**
- Statistics cards (Total Jurusan, Kelas, Siswa, Pelanggaran)
- Table with columns:
  - Nama Jurusan
  - Kode
  - Kaprodi
  - Total Kelas
  - Total Siswa
  - Total Pelanggaran
  - Pelanggaran Bulan Ini
  - Aksi (Detail button)

**resources/views/data_jurusan/show.blade.php:**
- Statistics cards (4 cards)
- Line chart: Pelanggaran per bulan (Chart.js)
- Doughnut chart: Pelanggaran per kategori (Chart.js)
- Table: Top 10 siswa with ranking badges (🥇🥈🥉)
- Link to siswa profile

#### Data Kelas Views
**resources/views/data_kelas/index.blade.php:**
- Statistics cards (Total Kelas, Siswa, Pelanggaran)
- Table with columns:
  - Nama Kelas
  - Tingkat
  - Jurusan
  - Wali Kelas
  - Total Siswa
  - Total Pelanggaran
  - Pelanggaran Bulan Ini
  - Aksi (Detail button)

**resources/views/data_kelas/show.blade.php:**
- Statistics cards (3 cards)
- Line chart: Pelanggaran per bulan (Chart.js)
- Doughnut chart: Pelanggaran per kategori (Chart.js)
- Table: Top 10 siswa with ranking badges (🥇🥈🥉)
- Link to siswa profile

### 4. Sidebar Navigation (resources/views/layouts/app.blade.php)

#### MONITORING DATA Section
- Updated to include Kepala Sekolah
- Data Siswa: Operator, Waka, Wali Kelas, Kaprodi only
- Riwayat Pelanggaran: All roles in section
- Data Jurusan: Waka Kesiswaan & Kepala Sekolah
- Data Kelas: Waka Kesiswaan & Kepala Sekolah

#### WAKA KESISWAAN Section
- Kelola Aturan & Rules
- Pembinaan Internal

#### KEPALA SEKOLAH Section
- Persetujuan Kasus
- Laporan & Ekspor
- Siswa Perlu Pembinaan
- (Data Siswa, Jurusan, Kelas moved to MONITORING DATA)

#### ADMINISTRASI Section (Operator Only)
- Data Pengguna (renamed from Manajemen User)
- Kelola Aturan & Rules
- Pembinaan Internal
- Audit & Log
- Kelola Jurusan
- Kelola Kelas

## Access Control Summary

### Waka Kesiswaan
**Can Manage (CRUD):**
- Frequency Rules
- Pembinaan Internal Rules

**Can View (Read-only with graphs):**
- Data Siswa
- Riwayat Pelanggaran
- Data Jurusan (with charts)
- Data Kelas (with charts)

**Can Do:**
- Catat Pelanggaran
- Riwayat Saya

### Kepala Sekolah
**Can View (Read-only with graphs):**
- Data Siswa
- Riwayat Pelanggaran
- Data Jurusan (with charts)
- Data Kelas (with charts)

**Can Manage:**
- Persetujuan Kasus
- Laporan & Ekspor
- Siswa Perlu Pembinaan

### Operator Sekolah
**Full CRUD Access:**
- All master data (Users, Jurusan, Kelas, Siswa)
- Frequency Rules
- Pembinaan Internal Rules
- Audit & Log

**Can View:**
- All data with full access

## Chart Implementation
- Using Chart.js CDN
- Line chart for pelanggaran per bulan (6 months)
- Doughnut chart for pelanggaran per kategori
- Responsive and maintainAspectRatio enabled
- Color scheme: Red/Pink for violations

## UI/UX Features
- Statistics cards with color coding:
  - Primary (blue): Jurusan/Kelas count
  - Success (green): Kelas count
  - Info (cyan): Siswa count
  - Warning (yellow): Total pelanggaran
  - Danger (red): Pelanggaran bulan ini
- Ranking badges for top 10 siswa (🥇🥈🥉)
- Responsive tables
- Bootstrap icons for actions
- Clean, informative layout

## Testing Checklist
- [x] Routes registered correctly
- [x] Controllers implemented
- [x] Views created with charts
- [x] Sidebar navigation updated
- [x] No diagnostics errors
- [ ] Manual testing as Waka Kesiswaan
- [ ] Manual testing as Kepala Sekolah
- [ ] Verify charts display correctly
- [ ] Verify statistics are accurate
- [ ] Verify links to siswa profiles work

## Files Created/Modified

### Created:
- app/Http/Controllers/DataKelasController.php
- resources/views/data_jurusan/index.blade.php
- resources/views/data_jurusan/show.blade.php
- resources/views/data_kelas/index.blade.php
- resources/views/data_kelas/show.blade.php

### Modified:
- routes/web.php
- resources/views/layouts/app.blade.php
- app/Http/Controllers/DataJurusanController.php (already existed, verified implementation)

## Notes
- Views are read-only for Waka & Kepsek (no CRUD buttons)
- Charts use last 6 months data
- Top 10 siswa sorted by total violations
- All statistics calculated from RiwayatPelanggaran table
- Sidebar organized by role sections for clarity
