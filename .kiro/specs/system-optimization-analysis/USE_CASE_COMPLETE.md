# Diagram Use Case - Sistem Kedisiplinan SMKN 1 Siak

## Gambaran Umum Sistem
**Nama Sistem**: SIMDIS (Sistem Informasi Manajemen Disiplin Siswa)  
**Tujuan**: Mengelola pencatatan pelanggaran siswa, tindak lanjut pembinaan, dan persetujuan kepala sekolah secara otomatis berbasis aturan

---

## Aktor (Pengguna Sistem)

### 1. **Operator Sekolah** (Administrator Sistem)
- Peran tertinggi dalam pengelolaan data
- Memiliki akses penuh ke semua fitur data induk
- Mengelola akun pengguna dan pengaturan sistem

### 2. **Kepala Sekolah**
- Menyetujui atau menolak tindak lanjut yang memerlukan persetujuan
- Memantau daftar siswa yang memerlukan pembinaan khusus
- Melihat laporan dan statistik sekolah

### 3. **Wakil Kepala Sekolah Bidang Kesiswaan** (Waka Kesiswaan)
- Memantau seluruh pelanggaran siswa di sekolah
- Mengakses laporan dan statistik kesiswaan
- Mengelola data siswa

### 4. **Kepala Program Studi** (Kaprodi)
- Memantau siswa di jurusan yang dipimpin
- Melihat statistik pelanggaran jurusan
- Menindaklanjuti pembinaan siswa jurusan

### 5. **Wali Kelas**
- Memantau siswa di kelas yang diampu
- Memperbarui nomor kontak wali murid siswa
- Mencatat pelanggaran siswa di kelasnya

### 6. **Guru**
- Mencatat pelanggaran siswa yang ditemukan
- Melihat riwayat pelanggaran siswa

### 7. **Wali Murid**
- Melihat pelanggaran yang dilakukan anaknya
- Melihat tindak lanjut pembinaan anaknya
- Menerima pemberitahuan pelanggaran

### 8. **Wakil Kepala Sekolah Bidang Sarana** (Waka Sarana)
- Mengakses dasbor khusus
- Mencatat pelanggaran terkait sarana prasarana

### 9. **Developer** (Pengembang Sistem)
- Meniru akun pengguna lain untuk debugging
- Mengakses semua fitur sistem
- Melakukan pemeliharaan sistem

---

## Use Cases by Module

### MODULE 1: AUTHENTICATION & AUTHORIZATION

#### UC-1.1: Login
**Actor**: Semua User  
**Description**: Login menggunakan username/email dan password  
**Flow**:
1. User input credentials
2. System validate credentials
3. System check is_active status
4. System redirect based on role
5. System log last_login_at

**Includes**:
- Validate Credentials
- Check Account Active
- Update Last Login

#### UC-1.2: Logout
**Actor**: Semua User  
**Description**: Logout dari sistem  
**Flow**:
1. User click logout
2. System invalidate session
3. System redirect to login

#### UC-1.3: Manage Profile
**Actor**: Semua User  
**Description**: Update profile (nama, email, username, password, foto)  
**Flow**:
1. User access profile page
2. User update information
3. System validate changes
4. System save changes
5. System log activity

**Includes**:
- Change Username
- Change Password
- Upload Photo

---

### MODULE 2: MASTER DATA MANAGEMENT

#### UC-2.1: Manage Users
**Actor**: Operator Sekolah  
**Description**: CRUD user accounts  
**Flow**:
1. Operator access user management
2. Operator create/edit/delete user
3. System validate data
4. System save changes
5. System log activity

**Includes**:
- Create User
- Edit User
- Delete User
- Toggle Active/Inactive
- Assign Role
- Assign Wali Kelas
- Assign Kaprodi

#### UC-2.2: Manage Siswa
**Actor**: Operator Sekolah, Waka Kesiswaan  
**Description**: CRUD data siswa  
**Flow**:
1. User access siswa management
2. User create/edit/delete siswa
3. System validate data
4. System auto-create wali murid account (optional)
5. System save changes
6. System log activity

**Includes**:
- Create Siswa (Single)
- Create Siswa (Bulk)
- Edit Siswa
- Delete Siswa
- Link Wali Murid Account
- Update Kontak Wali Murid (Wali Kelas)

#### UC-2.3: Manage Jurusan
**Actor**: Operator Sekolah  
**Description**: CRUD data jurusan  
**Flow**:
1. Operator access jurusan management
2. Operator create/edit/delete jurusan
3. System validate data
4. System save changes
5. System log activity

#### UC-2.4: Manage Kelas
**Actor**: Operator Sekolah  
**Description**: CRUD data kelas  
**Flow**:
1. Operator access kelas management
2. Operator create/edit/delete kelas
3. System validate data
4. System assign wali kelas
5. System save changes
6. System log activity

#### UC-2.5: Manage Jenis Pelanggaran
**Actor**: Operator Sekolah  
**Description**: CRUD jenis pelanggaran dan kategori  
**Flow**:
1. Operator access jenis pelanggaran management
2. Operator create/edit/delete jenis pelanggaran
3. System validate data
4. System check if used in riwayat (prevent delete)
5. System save changes
6. System log activity

**Includes**:
- Create Jenis Pelanggaran
- Edit Jenis Pelanggaran
- Delete Jenis Pelanggaran (if not used)
- Toggle Active/Inactive

---

### MODULE 3: RULES ENGINE CONFIGURATION

#### UC-3.1: Configure Frequency Rules
**Actor**: Operator Sekolah  
**Description**: Setup frequency-based rules untuk jenis pelanggaran  
**Flow**:
1. Operator select jenis pelanggaran
2. Operator add frequency rule (X kali dalam Y hari → Surat Z)
3. System validate rule
4. System auto-activate jenis pelanggaran
5. System save rule
6. System log activity

**Includes**:
- Add Frequency Rule
- Edit Frequency Rule
- Delete Frequency Rule
- Preview Rule Impact

#### UC-3.2: Configure Accumulation Rules
**Actor**: Operator Sekolah  
**Description**: Setup accumulation-based rules (threshold poin)  
**Flow**:
1. Operator access rules engine settings
2. Operator set threshold poin untuk setiap surat
3. System validate thresholds
4. System save settings
5. System log activity

**Includes**:
- Set Surat 1 Threshold
- Set Surat 2 Threshold
- Set Surat 3 Threshold
- Set Surat 4 Threshold
- Preview Impact

#### UC-3.3: Configure Pembinaan Internal Rules
**Actor**: Operator Sekolah  
**Description**: Setup pembinaan internal rules  
**Flow**:
1. Operator access pembinaan internal rules
2. Operator add rule (jenis pelanggaran + pembina tambahan)
3. System validate rule
4. System save rule
5. System log activity

**Includes**:
- Add Pembinaan Internal Rule
- Edit Pembinaan Internal Rule
- Delete Pembinaan Internal Rule

---

### MODULE 4: PELANGGARAN MANAGEMENT

#### UC-4.1: Catat Pelanggaran
**Actor**: Guru, Wali Kelas, Waka Kesiswaan, Kaprodi, Waka Sarana  
**Description**: Mencatat pelanggaran siswa (multi-select)  
**Flow**:
1. User access form catat pelanggaran
2. User select siswa (multiple)
3. User select jenis pelanggaran (multiple)
4. User input tanggal, jam, bukti foto, keterangan
5. User click preview (optional)
6. System show preview impact
7. User confirm and submit
8. System save riwayat pelanggaran
9. System trigger rules engine
10. System create/update tindak lanjut
11. System send notification (if needed)
12. System log activity

**Includes**:
- Select Siswa (with filters)
- Select Jenis Pelanggaran (with filters)
- Upload Bukti Foto
- Preview Impact (NEW)
- Trigger Rules Engine
- Send Notification

**Extends**:
- Filter Siswa by Tingkat/Jurusan/Kelas
- Filter Pelanggaran by Kategori
- Search Siswa
- Search Pelanggaran

#### UC-4.2: View Riwayat Pelanggaran
**Actor**: Semua User (except Wali Murid)  
**Description**: Melihat riwayat pelanggaran dengan filter  
**Flow**:
1. User access riwayat pelanggaran
2. User apply filters (siswa, tanggal, jenis, pencatat)
3. System show filtered results
4. User can view details
5. User can edit/delete (if authorized)

**Includes**:
- Filter by Siswa
- Filter by Tanggal
- Filter by Jenis Pelanggaran
- Filter by Pencatat
- View Details
- Edit Riwayat (Operator or Pencatat)
- Delete Riwayat (Operator or Pencatat)

#### UC-4.3: Edit Riwayat Pelanggaran
**Actor**: Operator Sekolah, Pencatat (owner)  
**Description**: Edit riwayat pelanggaran yang sudah dicatat  
**Flow**:
1. User access riwayat detail
2. User click edit
3. System check authorization
4. User update data
5. System validate changes
6. System re-trigger rules engine (if needed)
7. System save changes
8. System log activity

#### UC-4.4: Delete Riwayat Pelanggaran
**Actor**: Operator Sekolah, Pencatat (owner)  
**Description**: Hapus riwayat pelanggaran  
**Flow**:
1. User access riwayat detail
2. User click delete
3. System check authorization
4. System confirm deletion
5. System delete riwayat
6. System re-calculate tindak lanjut
7. System log activity

---

### MODULE 5: TINDAK LANJUT MANAGEMENT

#### UC-5.1: View Tindak Lanjut
**Actor**: Semua User (except Wali Murid)  
**Description**: Melihat daftar tindak lanjut dengan filter  
**Flow**:
1. User access tindak lanjut
2. User apply filters (siswa, status, surat)
3. System show filtered results
4. User can view details

**Includes**:
- Filter by Siswa
- Filter by Status
- Filter by Tipe Surat
- View Details

#### UC-5.2: Update Status Tindak Lanjut
**Actor**: Guru BK, Wali Kelas, Waka Kesiswaan, Kaprodi  
**Description**: Update status tindak lanjut  
**Flow**:
1. User access tindak lanjut detail
2. User select new status
3. User input catatan
4. System validate status transition
5. System save changes
6. System log activity

**Includes**:
- Change to "Dalam Proses"
- Change to "Selesai"
- Add Catatan

**Business Rules**:
- Cannot downgrade status
- Cannot change "Disetujui" status (only Kepsek)
- Cannot change "Menunggu Persetujuan" status (only Kepsek)

#### UC-5.3: Approve/Reject Tindak Lanjut
**Actor**: Kepala Sekolah  
**Description**: Approve atau reject tindak lanjut yang memerlukan persetujuan  
**Flow**:
1. Kepsek receive notification
2. Kepsek access tindak lanjut detail
3. Kepsek review case
4. Kepsek approve or reject
5. System update status
6. System send notification to pencatat
7. System log activity

**Includes**:
- Approve (status → "Disetujui")
- Reject (status → "Ditolak")
- Add Catatan Approval

---

### MODULE 6: REPORTING & MONITORING

#### UC-6.1: View Dashboard
**Actor**: Semua User  
**Description**: Melihat dashboard sesuai role  
**Flow**:
1. User login
2. System redirect to role-specific dashboard
3. System show statistics and charts
4. User can drill down to details

**Variants**:
- **Admin Dashboard**: Total siswa, pelanggaran, tindak lanjut, charts
- **Kepsek Dashboard**: Approval pending, siswa perlu pembinaan, statistics
- **Kaprodi Dashboard**: Statistics jurusan, siswa jurusan
- **Wali Kelas Dashboard**: Statistics kelas, siswa kelas
- **Wali Murid Dashboard**: Pelanggaran anak, tindak lanjut anak

#### UC-6.2: Generate Report
**Actor**: Kepala Sekolah, Waka Kesiswaan, Operator  
**Description**: Generate laporan pelanggaran  
**Flow**:
1. User access report page
2. User select report type
3. User set date range
4. User apply filters
5. System generate report
6. User preview report
7. User download PDF

**Includes**:
- Select Report Type
- Set Date Range
- Apply Filters
- Preview Report
- Download PDF

#### UC-6.3: View Siswa Perlu Pembinaan
**Actor**: Kepala Sekolah  
**Description**: Melihat daftar siswa yang perlu pembinaan khusus  
**Flow**:
1. Kepsek access siswa perlu pembinaan
2. System show siswa with active cases
3. System show siswa with high points
4. Kepsek can view details
5. Kepsek can take action

**Includes**:
- Filter by Jurusan
- Filter by Kelas
- View Details
- View Riwayat

---

### MODULE 7: NOTIFICATION SYSTEM

#### UC-7.1: Receive Notification
**Actor**: Kepala Sekolah  
**Description**: Menerima notifikasi untuk approval  
**Flow**:
1. System detect case needs approval
2. System send email notification
3. System create in-app notification
4. Kepsek see notification badge
5. Kepsek click notification
6. System redirect to tindak lanjut detail

**Includes**:
- Email Notification
- In-App Notification
- Notification Badge Counter

#### UC-7.2: View Notification List
**Actor**: Kepala Sekolah  
**Description**: Melihat daftar notifikasi  
**Flow**:
1. Kepsek click notification bell
2. System show notification list
3. Kepsek can mark as read
4. Kepsek can click to view detail

---

### MODULE 8: AUDIT & ACTIVITY LOG

#### UC-8.1: View Activity Log
**Actor**: Operator Sekolah  
**Description**: Melihat log aktivitas sistem  
**Flow**:
1. Operator access audit & log
2. Operator select tab (Activity, Last Login, Status)
3. Operator apply filters
4. System show filtered logs
5. Operator can view details
6. Operator can export CSV

**Includes**:
- Filter by Type
- Filter by User
- Filter by Date Range
- Search Description
- View Details
- Export CSV

#### UC-8.2: View Last Login
**Actor**: Operator Sekolah  
**Description**: Melihat last login semua user  
**Flow**:
1. Operator access audit & log → Last Login tab
2. Operator apply filters (role, search)
3. System show user list with last login
4. Operator can see relative time and full timestamp

#### UC-8.3: View Account Status
**Actor**: Operator Sekolah  
**Description**: Melihat status akun (active/inactive)  
**Flow**:
1. Operator access audit & log → Status tab
2. Operator apply filters (status, role, search)
3. System show user list with status
4. Operator can toggle active/inactive

---

### MODULE 9: FILE MANAGEMENT

#### UC-9.1: View Bukti Foto
**Actor**: Semua User (except Wali Murid)  
**Description**: Melihat bukti foto pelanggaran  
**Flow**:
1. User access riwayat detail
2. User click view photo
3. System show photo in modal/new tab
4. User can download photo

#### UC-9.2: Download Surat
**Actor**: Semua User (except Wali Murid)  
**Description**: Download surat panggilan (PDF)  
**Flow**:
1. User access tindak lanjut detail
2. User click download surat
3. System generate PDF
4. System download PDF to user device

---

## Use Case Relationships

### Include Relationships:
- **Catat Pelanggaran** includes **Trigger Rules Engine**
- **Trigger Rules Engine** includes **Create Tindak Lanjut**
- **Create Tindak Lanjut** includes **Send Notification** (if Kepsek involved)
- **Login** includes **Check Account Active**
- **All CRUD Operations** include **Log Activity**

### Extend Relationships:
- **View Riwayat** extends **Filter by Multiple Criteria**
- **View Tindak Lanjut** extends **Filter by Multiple Criteria**
- **Catat Pelanggaran** extends **Preview Impact** (optional)

### Generalization:
- **Manage Master Data** generalizes:
  - Manage Users
  - Manage Siswa
  - Manage Jurusan
  - Manage Kelas
  - Manage Jenis Pelanggaran

---

## System Boundaries

### Inside System:
- Authentication & Authorization
- Master Data Management
- Rules Engine
- Pelanggaran Recording
- Tindak Lanjut Management
- Reporting
- Notification
- Audit Log

### Outside System (External):
- Email Server (for notifications)
- File Storage (for photos)
- PDF Generator (for reports)

---

## Non-Functional Requirements

### Security:
- Role-based access control
- Account activation/deactivation
- Session management
- Activity logging

### Performance:
- Database indexes for fast queries
- Queued notifications
- Optimized rules engine

### Usability:
- Preview before submit
- Multi-select for efficiency
- Filters and search
- Responsive design

### Maintainability:
- Clean code architecture
- Service layer pattern
- Helper functions
- Comprehensive documentation

---

## Use Case Priority

### High Priority (Core Features):
1. Login/Logout
2. Catat Pelanggaran
3. View Riwayat
4. Trigger Rules Engine
5. Approve/Reject Tindak Lanjut
6. View Dashboard

### Medium Priority:
1. Manage Master Data
2. Configure Rules Engine
3. Generate Reports
4. Notification System
5. Update Status Tindak Lanjut

### Low Priority:
1. Audit & Activity Log
2. Profile Management
3. File Management
4. Developer Tools

---

## Use Case Diagram Structure (for draw.io)

```
┌─────────────────────────────────────────────────────────────┐
│                    SIMDIS - Use Case Diagram                 │
└─────────────────────────────────────────────────────────────┘

Actors (Left Side):
├── Operator Sekolah
├── Kepala Sekolah
├── Waka Kesiswaan
├── Kaprodi
├── Wali Kelas
├── Guru
├── Wali Murid
└── Developer

System Boundary (Center):
┌─────────────────────────────────────────────────────────────┐
│                         SIMDIS System                        │
│                                                              │
│  Authentication Module:                                      │
│  ○ Login                                                     │
│  ○ Logout                                                    │
│  ○ Manage Profile                                            │
│                                                              │
│  Master Data Module:                                         │
│  ○ Manage Users                                              │
│  ○ Manage Siswa                                              │
│  ○ Manage Jurusan                                            │
│  ○ Manage Kelas                                              │
│  ○ Manage Jenis Pelanggaran                                  │
│                                                              │
│  Rules Engine Module:                                        │
│  ○ Configure Frequency Rules                                 │
│  ○ Configure Accumulation Rules                              │
│  ○ Configure Pembinaan Internal Rules                        │
│                                                              │
│  Pelanggaran Module:                                         │
│  ○ Catat Pelanggaran                                         │
│  ○ View Riwayat Pelanggaran                                  │
│  ○ Edit Riwayat Pelanggaran                                  │
│  ○ Delete Riwayat Pelanggaran                                │
│                                                              │
│  Tindak Lanjut Module:                                       │
│  ○ View Tindak Lanjut                                        │
│  ○ Update Status Tindak Lanjut                               │
│  ○ Approve/Reject Tindak Lanjut                              │
│                                                              │
│  Reporting Module:                                           │
│  ○ View Dashboard                                            │
│  ○ Generate Report                                           │
│  ○ View Siswa Perlu Pembinaan                                │
│                                                              │
│  Notification Module:                                        │
│  ○ Receive Notification                                      │
│  ○ View Notification List                                    │
│                                                              │
│  Audit Module:                                               │
│  ○ View Activity Log                                         │
│  ○ View Last Login                                           │
│  ○ View Account Status                                       │
│                                                              │
└─────────────────────────────────────────────────────────────┘

External Systems (Right Side):
├── Email Server
├── File Storage
└── PDF Generator
```

---

## How to Create in draw.io

### Step 1: Create Actors
1. Drag "Actor" shape from UML library
2. Create 9 actors on left side
3. Label each actor

### Step 2: Create System Boundary
1. Drag "Rectangle" shape
2. Label as "SIMDIS System"
3. Place in center

### Step 3: Create Use Cases
1. Drag "Use Case" (oval) shapes
2. Place inside system boundary
3. Label each use case
4. Group by module

### Step 4: Create Associations
1. Draw lines from actors to use cases
2. Use solid lines for associations
3. Use dashed arrows for <<include>>
4. Use dashed arrows for <<extend>>

### Step 5: Add External Systems
1. Create actors on right side
2. Label as external systems
3. Draw associations to relevant use cases

---

## Summary

**Total Use Cases**: 35+  
**Total Actors**: 9  
**Total Modules**: 9  
**Complexity**: Medium-High  

**Key Features**:
- Multi-role access control
- Rules engine automation
- Approval workflow
- Notification system
- Comprehensive reporting
- Audit trail

**System Type**: Web-based Management Information System  
**Architecture**: MVC with Service Layer  
**Framework**: Laravel 12
