# 🚀 Deployment Checklist - System Optimization Phase 1

## Pre-Deployment Verification

### 1. Code Quality ✅
- [x] All files pass syntax checks
- [x] No diagnostic errors
- [x] Clean code principles applied
- [x] No spaghetti code
- [x] Consistent naming conventions

### 2. Database ✅
- [x] Migration file created: `2025_12_07_141805_add_performance_indexes_to_tables.php`
- [x] Notifications table migration exists
- [x] All migrations tested locally

### 3. Files Modified/Created

#### Backend Files:
- [x] `app/Services/Pelanggaran/PelanggaranRulesEngine.php` - Approval logic refactored
- [x] `app/Services/Pelanggaran/PelanggaranPreviewService.php` - NEW
- [x] `app/Services/Notification/TindakLanjutNotificationService.php` - NEW
- [x] `app/Notifications/KasusButuhApprovalNotification.php` - NEW
- [x] `app/Http/Controllers/Pelanggaran/PelanggaranController.php` - Preview method added
- [x] `database/migrations/2025_12_07_141805_add_performance_indexes_to_tables.php` - NEW

#### Frontend Files:
- [x] `resources/views/layouts/app.blade.php` - Notification bell added
- [x] `resources/views/pelanggaran/create.blade.php` - Preview button & modal added
- [x] `resources/views/pelanggaran/partials/preview-modal.blade.php` - NEW
- [x] `public/js/pages/pelanggaran/create.js` - Preview functionality added

#### Routes:
- [x] `routes/web.php` - Preview route added

#### Bug Fixes:
- [x] `resources/views/siswa/index.blade.php` - Removed audit.siswa link
- [x] `resources/views/dashboards/operator.blade.php` - Removed audit section

---

## Deployment Steps

### Step 1: Backup 🔒
```bash
# Backup database
php artisan backup:run

# Or manual backup
mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql

# Backup files
tar -czf backup_files_$(date +%Y%m%d_%H%M%S).tar.gz app/ resources/ public/ routes/
```

### Step 2: Pull Code 📥
```bash
# Pull from repository
git pull origin main

# Or upload files manually if not using git
# Upload all modified files listed above
```

### Step 3: Install Dependencies 📦
```bash
# Composer dependencies (if any new packages)
composer install --no-dev --optimize-autoloader

# NPM dependencies (if any frontend changes)
npm install
npm run production
```

### Step 4: Run Migrations 🗄️
```bash
# Run migrations
php artisan migrate

# Verify migrations
php artisan migrate:status
```

### Step 5: Clear Caches 🧹
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 6: Queue Configuration ⚙️
```bash
# Ensure queue worker is running for notifications
php artisan queue:work --daemon

# Or use supervisor for production
# Add to supervisor config:
# [program:laravel-worker]
# command=php /path/to/artisan queue:work --sleep=3 --tries=3
# autostart=true
# autorestart=true
```

### Step 7: Verify Deployment ✅
- [ ] Homepage loads without errors
- [ ] Login works for all roles
- [ ] Dashboard loads for Operator
- [ ] Dashboard loads for Kepala Sekolah
- [ ] Pelanggaran form loads
- [ ] Preview button appears
- [ ] Notification bell appears in navbar

---

## Testing Checklist

### 1. Database Indexes
```sql
-- Verify indexes were created
SHOW INDEX FROM riwayat_pelanggaran;
SHOW INDEX FROM tindak_lanjut;
SHOW INDEX FROM siswa;
SHOW INDEX FROM kelas;
```

Expected indexes:
- [x] idx_riwayat_siswa_tanggal
- [x] idx_riwayat_siswa_jenis
- [x] idx_riwayat_tanggal
- [x] idx_riwayat_pencatat
- [x] idx_tindaklanjut_status
- [x] idx_tindaklanjut_siswa_status
- [x] idx_siswa_kelas
- [x] idx_kelas_jurusan

### 2. Notification System
- [ ] Login as Guru/Wali Kelas
- [ ] Record violation that triggers Kepala Sekolah involvement
- [ ] Verify notification appears in bell icon
- [ ] Verify badge counter shows correct number
- [ ] Login as Kepala Sekolah
- [ ] Verify notification received
- [ ] Check email was sent (if configured)
- [ ] Verify notification stored in database

### 3. Preview Feature
- [ ] Open pelanggaran form
- [ ] Select students and violations
- [ ] Click "PREVIEW DAMPAK" button
- [ ] Verify loading state appears
- [ ] Verify modal opens with content
- [ ] Test scenario: No warnings (should show success message)
- [ ] Test scenario: Existing active case (should show warning)
- [ ] Test scenario: Threshold trigger (should show warning)
- [ ] Test scenario: Pembinaan internal (should show info)
- [ ] Verify confirmation checkbox appears for high-impact
- [ ] Verify "Lanjutkan Pencatatan" button works
- [ ] Verify form submits correctly after preview

### 4. Approval Logic
Test scenarios:

**Scenario 1: Surat 1 (No Kepsek)**
- [ ] Record violation → Accumulation triggers Surat 1
- [ ] Verify pembina: Wali Kelas, Guru BK
- [ ] Verify status: "Baru" (no approval needed)

**Scenario 2: Surat 2 (No Kepsek)**
- [ ] Record violation → Accumulation triggers Surat 2
- [ ] Verify pembina: Wali Kelas, Guru BK, Waka Kesiswaan
- [ ] Verify status: "Baru" (no approval needed)

**Scenario 3: Surat 2 (With Kepsek via Pembinaan Internal)**
- [ ] Record violation → Pembinaan Internal adds Kepsek
- [ ] Verify pembina: Wali Kelas, Guru BK, Waka Kesiswaan, Kepala Sekolah
- [ ] Verify status: "Menunggu Persetujuan" ✅
- [ ] Verify notification sent to Kepsek

**Scenario 4: Surat 3 (No Kepsek)**
- [ ] Record violation → Accumulation triggers Surat 3
- [ ] Verify pembina: Wali Kelas, Guru BK, Waka Kesiswaan, Kaprodi
- [ ] Verify status: "Baru" (no approval needed) ✅ NEW BEHAVIOR

**Scenario 5: Surat 3 (With Kepsek)**
- [ ] Record violation → Accumulation triggers Surat 3 with Kepsek
- [ ] Verify pembina includes: Kepala Sekolah
- [ ] Verify status: "Menunggu Persetujuan" ✅
- [ ] Verify notification sent to Kepsek

**Scenario 6: Surat 4**
- [ ] Record violation → Accumulation triggers Surat 4
- [ ] Verify pembina: All including Kepala Sekolah
- [ ] Verify status: "Menunggu Persetujuan" ✅
- [ ] Verify notification sent to Kepsek

### 5. Bug Fixes
- [ ] Login as Operator
- [ ] Navigate to Dashboard
- [ ] Verify no "Audit & Manajemen" section
- [ ] Navigate to Siswa index
- [ ] Verify no "Audit & Hapus" button
- [ ] No route errors in browser console

---

## Rollback Plan 🔄

If issues occur:

### Quick Rollback:
```bash
# Rollback last migration
php artisan migrate:rollback --step=1

# Restore code from backup
git reset --hard HEAD~1

# Or restore files manually
tar -xzf backup_files_TIMESTAMP.tar.gz

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Database Rollback:
```bash
# Restore database from backup
mysql -u username -p database_name < backup_TIMESTAMP.sql
```

---

## Post-Deployment Monitoring

### Day 1-3: Intensive Monitoring
- [ ] Monitor error logs: `storage/logs/laravel.log`
- [ ] Monitor queue jobs: `php artisan queue:failed`
- [ ] Check notification delivery rate
- [ ] Monitor database query performance
- [ ] Gather user feedback

### Week 1: Performance Metrics
- [ ] Measure query performance improvement
- [ ] Track notification delivery success rate
- [ ] Monitor preview feature usage
- [ ] Track approval workflow completion time

### Week 2-4: User Feedback
- [ ] Survey users on new features
- [ ] Identify pain points
- [ ] Plan Phase 2 improvements

---

## Support Contacts

**Technical Issues**:
- Developer: [Your contact]
- System Admin: [Admin contact]

**User Training**:
- Schedule training sessions for:
  - Kepala Sekolah (notification system)
  - Guru/Wali Kelas (preview feature)
  - All users (approval logic changes)

---

## Success Criteria ✅

Deployment is successful when:
- [x] All migrations run without errors
- [ ] No error logs in first 24 hours
- [ ] All test scenarios pass
- [ ] Users can access all features
- [ ] Notifications are delivered
- [ ] Preview feature works correctly
- [ ] Approval logic behaves as expected
- [ ] No performance degradation

---

## Documentation Updated

- [x] FINAL_SUMMARY.md
- [x] PREVIEW_FEATURE_COMPLETE.md
- [x] RUNTIME_FIXES.md
- [x] APPROVAL_LOGIC_REFACTORING.md
- [x] DEPLOYMENT_CHECKLIST.md (this file)

---

**Deployment Date**: _____________  
**Deployed By**: _____________  
**Verified By**: _____________  
**Status**: _____________
