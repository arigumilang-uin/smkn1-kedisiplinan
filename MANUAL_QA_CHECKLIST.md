# 📋 MANUAL QA CHECKLIST
## Critical Testing Scenarios for Production Release

**Version:** 1.0.0  
**Date:** 2025-12-08  
**Tester:** _______________  
**Environment:** Production / Staging  

---

## ⚠️ IMPORTANT NOTES

- Test in **exact production environment** (or staging that mirrors production)
- Use **fresh browser** (incognito/private mode) for each test
- **Document all failures** with screenshots
- Test with **different user roles**
- Verify **database changes** after each action

---

## 🔐 TEST 1: AUTHENTICATION & AUTHORIZATION

### 1.1 Login Flow
**Priority:** CRITICAL  
**Role:** Any  

**Steps:**
1. Navigate to: `http://your-domain.com`
2. Should redirect to login page automatically
3. Enter credentials:
   - Username: `admin` (or your operator username)
   - Password: (your password)
4. Click "Login"

**Expected Results:**
- ✅ No 500 errors
- ✅ Redirects to role-appropriate dashboard
- ✅ Username displayed in top-right corner
- ✅ Sidebar menu loads correctly
- ✅ Dashboard statistics show (counts, charts)

**Verify in Database:**
```sql
SELECT last_login_at FROM users WHERE username = 'admin';
-- Should show current timestamp
```

**Pass/Fail:** [ PASS ] [ FAIL ]  
**Notes:** ___________________________________

---

### 1.2 Role-Based Dashboard Access
**Priority:** HIGH  
**Role:** Multiple  

**Steps:**
1. Login as **Operator Sekolah**
   - Dashboard URL: `/dashboard/admin`
   - Should see: User stats, siswa count, class count
2. Logout
3. Login as **Kepala Sekolah**
   - Dashboard URL: `/dashboard/kepsek`
   - Should see: Approval queue, reports
4. Logout
5. Login as **Wali Kelas**
   - Dashboard URL: `/dashboard/walikelas`
   - Should see: Class students, recent violations

**Expected Results:**
- ✅ Each role sees different dashboard
- ✅ Statistics load correctly
- ✅ No unauthorized access errors
- ✅ Menus show role-appropriate options

**Pass/Fail:** [ PASS ] [ FAIL ]  
**Notes:** ___________________________________

---

## 👨‍🎓 TEST 2: CREATE SISWA (+ AUTO WALI MURID ACCOUNT)

### 2.1 Create New Student
**Priority:** CRITICAL  
**Role:** Operator Sekolah  

**Steps:**
1. Login as **Operator Sekolah**
2. Navigate to: **Data Siswa** → **Tambah Siswa**
   - URL: `/siswa/create`
3. Fill form:
   ```
   NISN: 1234567890
   Nama: Test Siswa QA
   Kelas: (select any, e.g., X TIF 1)
   Tanggal Lahir: 2007-01-15
   Jenis Kelamin: Laki-laki
   Alamat: Jl. Test No. 123
   
   === WALI MURID ===
   Nama Wali: Bapak Test QA
   No HP Wali: 081234567890
   Email Wali: test.qa@example.com
   ```
4. Check: "Buat akun Wali Murid otomatis" ✓
5. Click **Simpan**

**Expected Results:**
- ✅ Success message shown
- ✅ Flash message shows Wali Murid credentials
- ✅ Redirects to siswa list
- ✅ New siswa appears in table
- ✅ Wali Murid column shows phone number with WhatsApp link

**Verify in Database:**
```sql
-- Check siswa created
SELECT * FROM siswa WHERE nisn = '1234567890';
-- Should return 1 row

-- Check Wali Murid user created
SELECT id, username, email, role_id FROM users 
WHERE email = 'test.qa@example.com';
-- Should return 1 row with role = 'Wali Murid'

-- Check relationship
SELECT s.nama_siswa, u.username as wali_username
FROM siswa s
LEFT JOIN users u ON s.wali_murid_user_id = u.id
WHERE s.nisn = '1234567890';
-- Should show siswa with linked wali username
```

**Manual Verification:**
1. Copy the auto-generated Wali Murid credentials from success message
2. Logout
3. Login with those credentials
4. Should see **Wali Murid dashboard**
5. Should see the child's violation records (initially empty)

**Pass/Fail:** [ PASS ] [ FAIL ]  
**Notes:** ___________________________________

---

## 🚨 TEST 3: RECORD VIOLATION (Hybrid Logic Check)

### 3.1 Create Violation Record
**Priority:** CRITICAL  
**Role:** Guru / Waka Kesiswaan  

**Steps:**
1. Login as **Guru** or **Waka Kesiswaan**
2. Navigate to: **Catat Pelanggaran** (or **Riwayat Pelanggaran** → **Tambah**)
   - URL: `/riwayat/create`
3. Fill form:
   ```
   Siswa: (search and select "Test Siswa QA" created in Test 2)
   Jenis Pelanggaran: (select any, e.g., "Terlambat")
   Tanggal Kejadian: (today's date)
   Waktu: 07:30
   Keterangan: Terlambat 30 menit tanpa keterangan
   Bukti Foto: (optional - upload if testing file uploads)
   ```
4. Click **Simpan**

**Expected Results:**
- ✅ Success message shown
- ✅ Redirects to riwayat list
- ✅ New violation appears in table
- ✅ Shows: Siswa name, violation type, date, point value
- ✅ Bukti foto link works (if uploaded)

**Verify in Database:**
```sql
-- Check violation recorded
SELECT rp.*, s.nama_siswa, jp.nama_pelanggaran, jp.poin
FROM riwayat_pelanggaran rp
JOIN siswa s ON rp.siswa_id = s.id
JOIN jenis_pelanggaran jp ON rp.jenis_pelanggaran_id = jp.id
WHERE s.nisn = '1234567890'
ORDER BY rp.created_at DESC
LIMIT 1;
-- Should show the newly created violation
```

**Pass/Fail:** [ PASS ] [ FAIL ]  
**Notes:** ___________________________________

---

### 3.2 Verify Hybrid Logic (Auto Tindak Lanjut Trigger)
**Priority:** CRITICAL  
**Role:** System (Automatic)  

**Context:** System should automatically check if student's total points exceed threshold and create Tindak Lanjut record.

**Steps:**
1. After creating violation in 3.1, navigate to: **Tindak Lanjut**
   - URL: `/tindak-lanjut`
2. Filter by siswa: "Test Siswa QA"

**Expected Results (depends on point threshold):**

**IF total points < threshold:**
- ✅ No Tindak Lanjut auto-created
- ✅ This is correct behavior

**IF total points >= threshold (e.g., 3 violations of 25 points each = 75):**
- ✅ Tindak Lanjut record auto-created
- ✅ Status: "Baru" or "Menunggu Persetujuan"
- ✅ Jenis: "Panggilan Orang Tua" (or appropriate type based on points)
- ✅ Description mentions accumulated points

**To Trigger Auto-Creation (if needed):**
1. Create 3-4 violations for same student
2. Each with medium/high points (e.g., 25-50 points each)
3. Check Tindak Lanjut list after each violation
4. Should auto-create when threshold exceeded

**Verify in Database:**
```sql
-- Check if Tindak Lanjut auto-created
SELECT tl.*, s.nama_siswa, tl.status, tl.jenis_tindak_lanjut
FROM tindak_lanjut tl
JOIN siswa s ON tl.siswa_id = s.id
WHERE s.nisn = '1234567890'
ORDER BY tl.created_at DESC;
-- Should show auto-created record if threshold exceeded
```

**Pass/Fail:** [ PASS ] [ FAIL ]  
**Notes:** ___________________________________

---

## ✅ TEST 4: TINDAK LANJUT WORKFLOW (Approval & Completion)

### 4.1 Create Manual Tindak Lanjut
**Priority:** HIGH  
**Role:** Waka Kesiswaan  

**Steps:**
1. Login as **Waka Kesiswaan**
2. Navigate to: **Tindak Lanjut** → **Tambah**
   - URL: `/tindak-lanjut/create`
3. Fill form:
   ```
   Siswa: (select "Test Siswa QA")
   Jenis Tindak Lanjut: Panggilan Orang Tua
   Keterangan: Panggilan untuk pembinaan perilaku
   Tanggal Rencana: (tomorrow's date)
   ```
4. Click **Simpan**

**Expected Results:**
- ✅ Success message
- ✅ Status: "Menunggu Persetujuan" (if Kepsek approval required)
- ✅ OR Status: "Baru" (if direct creation allowed)

**Verify in Database:**
```sql
SELECT * FROM tindak_lanjut 
WHERE siswa_id = (SELECT id FROM siswa WHERE nisn = '1234567890')
ORDER BY created_at DESC
LIMIT 1;
```

**Pass/Fail:** [ PASS ] [ FAIL ]  
**Notes:** ___________________________________

---

### 4.2 Approval Flow (Kepala Sekolah)
**Priority:** HIGH  
**Role:** Kepala Sekolah  

**Steps:**
1. Logout from Waka account
2. Login as **Kepala Sekolah**
3. Navigate to: **Dashboard** or **Persetujuan**
   - Should see notification: "X Kasus Menunggu Persetujuan"
4. Click **Lihat** or navigate to: `/kepala-sekolah/approvals`
5. Find the Tindak Lanjut for "Test Siswa QA"
6. Click **Review** or **Detail**
7. Read details, then click **Setujui**
8. (Optional) Add approval notes: "Disetujui untuk ditindaklanjuti"
9. Confirm approval

**Expected Results:**
- ✅ Status changes to: "Disetujui"
- ✅ Success message shown
- ✅ Approved_by filled with Kepsek name
- ✅ Approved_at filled with timestamp
- ✅ Case removed from approval queue

**Verify in Database:**
```sql
SELECT 
    tl.status,
    tl.approved_at,
    tl.approval_notes,
    u.nama as approved_by_name
FROM tindak_lanjut tl
LEFT JOIN users u ON tl.approved_by_user_id = u.id
WHERE tl.siswa_id = (SELECT id FROM siswa WHERE nisn = '1234567890')
ORDER BY tl.created_at DESC
LIMIT 1;
-- Status should be 'Disetujui', approved_at should be current timestamp
```

**Pass/Fail:** [ PASS ] [ FAIL ]  
**Notes:** ___________________________________

---

### 4.3 Complete Tindak Lanjut
**Priority:** HIGH  
**Role:** Waka Kesiswaan / Wali Kelas

**Steps:**
1. Login as **Waka Kesiswaan** or **Wali Kelas**
2. Navigate to: **Tindak Lanjut**
3. Find the approved case for "Test Siswa QA"
4. Click **Edit** or **Detail**
5. Update:
   ```
   Status: Selesai
   Hasil Tindak Lanjut: Orang tua datang dan berkomitmen untuk membimbing
   Tanggal Selesai: (today's date)
   ```
6. (Optional) Generate **Surat Panggilan** if feature available:
   - Click **Cetak Surat** or **Generate PDF**
   - Should download PDF with parent summons letter
7. Click **Simpan**

**Expected Results:**
- ✅ Status changes to: "Selesai"
- ✅ Completion timestamp recorded
- ✅ Case marked as resolved
- ✅ PDF generation works (if tested)
- ✅ Case removed from active queue

**Verify in Database:**
```sql
SELECT 
    status,
    hasil_tindak_lanjut,
    tanggal_selesai,
    completed_at
FROM tindak_lanjut
WHERE siswa_id = (SELECT id FROM siswa WHERE nisn = '1234567890')
ORDER BY created_at DESC
LIMIT 1;
-- Status should be 'Selesai', completed_at should be filled
```

**Pass/Fail:** [ PASS ] [ FAIL ]  
**Notes:** ___________________________________

---

## 📊 TEST 5: REPORTS & STATISTICS

### 5.1 Dashboard Statistics
**Priority:** MEDIUM  
**Role:** Operator Sekolah / Waka Kesiswaan  

**Steps:**
1. Login as **Operator Sekolah** or **Waka Kesiswaan**
2. View **Dashboard** (`/dashboard/admin`)
3. Check all statistics display correctly:
   - Total Users
   - Total Siswa
   - Total Kelas
   - Total Aturan Pelanggaran
   - Recent violations chart
   - Active cases count

**Expected Results:**
- ✅ All counts are numbers (not errors)
- ✅ Charts render (if present)
- ✅ "Kasus Aktif" count matches database
- ✅ "Recent violations" table shows recent data
- ✅ Filters work (date range, class, major)

**Verify Sample:**
```sql
-- Verify a few stats
SELECT COUNT(*) as total_siswa FROM siswa WHERE deleted_at IS NULL;
SELECT COUNT(*) as total_kelas FROM kelas;
SELECT COUNT(*) FROM tindak_lanjut 
WHERE status IN ('Baru', 'Menunggu Persetujuan', 'Disetujui', 'Ditangani');
```

**Pass/Fail:** [ PASS ] [ FAIL ]  
**Notes:** ___________________________________

---

### 5.2 Report Generation (Kepala Sekolah)
**Priority:** MEDIUM  
**Role:** Kepala Sekolah  

**Steps:**
1. Login as **Kepala Sekolah**
2. Navigate to: **Laporan** or **Reports** (`/kepala-sekolah/reports`)
3. Select: **Laporan Pelanggaran**
4. Set filters:
   - Date: Last 30 days
   - Class: (any)
   - Violation Type: (all)
5. Click **Generate** or **Lihat Laporan**
6. (Optional) Click **Export Excel** or **Export PDF**

**Expected Results:**
- ✅ Report loads without errors
- ✅ Table shows violation records
- ✅ Filters work correctly
- ✅ Export generates downloadable file (if tested)
- ✅ Data matches database

**Pass/Fail:** [ PASS ] [ FAIL ]  
**Notes:** ___________________________________

---

## 🔗 BONUS TESTS (Optional but Recommended)

### B1: File Upload (Bukti Foto)
**Steps:**
1. Create violation with photo evidence
2. Upload image (JPG/PNG, < 2MB)
3. Verify image displays correctly
4. Verify file saved in `storage/app/public/`
5. Verify route `bukti.show` works

**Pass/Fail:** [ PASS ] [ FAIL ]

---

### B2: Legacy Route Compatibility
**Steps:**
1. Try old route names:
   - `/kasus/1/edit` (should work via legacy adapter)
   - `/my-riwayat` (should work)
   - `/data-jurusan` (should redirect)
2. All should work or redirect gracefully

**Pass/Fail:** [ PASS ] [ FAIL ]

---

### B3: Profile Management
**Steps:**
1. Login as any user
2. Click username → **Edit Profile** (`/profile/edit`)
3. Update: Name, email, phone
4. Click **Simpan**
5. Verify changes saved

**Pass/Fail:** [ PASS ] [ FAIL ]

---

### B4: Password Change
**Steps:**
1. Navigate to: **Change Password** (`/profile/change-password`)
2. Enter:
   - Current password
   - New password
   - Confirm new password
3. Click **Update Password**
4. Logout
5. Login with new password

**Pass/Fail:** [ PASS ] [ FAIL ]

---

## 📝 OVERALL TEST SUMMARY

| Test | Priority | Status | Notes |
|------|----------|--------|-------|
| 1.1 Login Flow | CRITICAL | [ ] | |
| 1.2 Role Dashboards | HIGH | [ ] | |
| 2.1 Create Siswa | CRITICAL | [ ] | |
| 3.1 Record Violation | CRITICAL | [ ] | |
| 3.2 Hybrid Logic | CRITICAL | [ ] | |
| 4.1 Create Tindak Lanjut | HIGH | [ ] | |
| 4.2 Approval Flow | HIGH | [ ] | |
| 4.3 Complete Case | HIGH | [ ] | |
| 5.1 Dashboard Stats | MEDIUM | [ ] | |
| 5.2 Reports | MEDIUM | [ ] | |

**Critical Tests Passed:** ___ / 5  
**All Tests Passed:** ___ / 10  

---

## ✅ FINAL CHECKLIST

Before approving for production:

- [ ] All CRITICAL tests passed
- [ ] No 500 errors encountered
- [ ] Database integrity verified
- [ ] File uploads working (if tested)
- [ ] All user roles tested
- [ ] Performance acceptable (pages load < 3 seconds)
- [ ] No console errors in browser
- [ ] Mobile responsive (basic check)

---

## 🚨 FAILURE PROTOCOL

If ANY critical test fails:

1. **STOP** deployment immediately
2. Document the failure with:
   - Exact steps to reproduce
   - Error message/screenshot
   - Expected vs actual behavior
3. Report to development team
4. **DO NOT deploy to production**
5. Retest after fix

---

## ✅ SIGN-OFF

**Tester Name:** _______________  
**Date Tested:** _______________  
**Environment:** _______________  
**Overall Result:** [ PASS ] [ FAIL ]  

**Approval for Production:** [ YES ] [ NO ]  

**Signature:** _______________

---

**Notes:**
- This checklist covers the TOP 5 CRITICAL flows
- Additional testing recommended for complete QA
- Perform regression testing after any fixes
- Keep this document for audit trail

---

**Version:** 1.0  
**Last Updated:** 2025-12-08  
**Prepared By:** Senior Release Manager
