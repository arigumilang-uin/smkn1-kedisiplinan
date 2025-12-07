# 🚀 QUICK REFERENCE - System Optimization

**For**: Developers & Operations  
**Last Updated**: 7 Desember 2025

---

## 📋 APPROVAL LOGIC (NEW)

### **Business Rule**:
```
IF "Kepala Sekolah" IN pembina_roles:
    → Status: "Menunggu Persetujuan"
    → Notification: SENT
ELSE:
    → Status: "Baru"
    → Notification: NOT SENT
```

### **Code Location**:
```php
// app/Services/Pelanggaran/PelanggaranRulesEngine.php
private function tentukanStatusBerdasarkanPembina(array $pembinaRoles): string
{
    if (in_array('Kepala Sekolah', $pembinaRoles)) {
        return 'Menunggu Persetujuan';
    }
    return 'Baru';
}
```

### **Used In**:
1. `processBatch()` - Line 108
2. `reconcileForSiswa()` - Line 490, 497
3. `eskalasiBilaPerluan()` - Line 579

---

## 🗂️ FILE STRUCTURE

```
app/
├── Notifications/
│   └── KasusButuhApprovalNotification.php (NEW)
├── Services/
│   ├── Notification/
│   │   └── TindakLanjutNotificationService.php (NEW)
│   └── Pelanggaran/
│       ├── PelanggaranRulesEngine.php (MODIFIED)
│       └── PelanggaranPreviewService.php (NEW)
└── Http/Controllers/Pelanggaran/
    └── PelanggaranController.php (MODIFIED)

resources/views/
├── layouts/
│   └── app.blade.php (MODIFIED - notification bell)
└── pelanggaran/partials/
    └── preview-modal.blade.php (NEW)

database/migrations/
└── 2025_12_07_141805_add_performance_indexes_to_tables.php (NEW)
```

---

## 🔧 DEPLOYMENT COMMANDS

```bash
# 1. Backup database
mysqldump -u root -p db_smkn1_kedisiplinan > backup.sql

# 2. Run migrations
php artisan migrate

# 3. Verify indexes
mysql -u root -p -e "SHOW INDEX FROM riwayat_pelanggaran;"

# 4. Clear cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 5. Start queue (choose one)
# Option A: Supervisor (production)
sudo supervisorctl start laravel-worker:*

# Option B: Cron (simple)
php artisan queue:work --daemon

# 6. Test notification
php artisan tinker
>>> $kepsek = User::whereHas('role', fn($q) => $q->where('nama_role', 'Kepala Sekolah'))->first();
>>> $tl = TindakLanjut::where('status', 'Menunggu Persetujuan')->first();
>>> $kepsek->notify(new \App\Notifications\KasusButuhApprovalNotification($tl));
```

---

## 🧪 TESTING CHECKLIST

### **Quick Smoke Test**:
```bash
# 1. Test database indexes
php artisan tinker
>>> DB::select("EXPLAIN SELECT * FROM riwayat_pelanggaran WHERE siswa_id = 1 AND jenis_pelanggaran_id = 1");
# Expected: key = 'idx_riwayat_siswa_jenis'

# 2. Test notification
>>> $kepsek = User::role('Kepala Sekolah')->first();
>>> $kepsek->unreadNotifications()->count();
# Expected: number of unread notifications

# 3. Test approval logic
>>> $tl = TindakLanjut::with('suratPanggilan')->first();
>>> $tl->suratPanggilan->pembina_data;
# Check if "Kepala Sekolah" in array
>>> $tl->status;
# Expected: "Menunggu Persetujuan" if Kepsek in pembina_data
```

### **Manual UI Test**:
1. Login as Kepala Sekolah
2. Check navbar → Bell icon should show badge if unread
3. Click bell → Dropdown should show notifications
4. Navigate to `/kepala-sekolah/approvals`
5. Should see list of cases with status "Menunggu Persetujuan"

---

## 🐛 TROUBLESHOOTING

### **Issue**: Notification not sent
```bash
# Check queue
php artisan queue:work --once

# Check failed jobs
php artisan queue:failed

# Retry failed
php artisan queue:retry all
```

### **Issue**: Badge counter not updating
```bash
# Clear cache
php artisan cache:clear

# Check notifications
php artisan tinker
>>> User::find(1)->unreadNotifications()->count();
```

### **Issue**: Wrong status assigned
```bash
# Check pembina_data
php artisan tinker
>>> $tl = TindakLanjut::with('suratPanggilan')->find(1);
>>> $tl->suratPanggilan->pembina_data;
# Verify "Kepala Sekolah" presence

# Check status
>>> $tl->status;
# Should be "Menunggu Persetujuan" if Kepsek in pembina_data
```

---

## 📊 MONITORING

### **Performance Metrics**:
```sql
-- Check slow queries
SELECT * FROM mysql.slow_log ORDER BY start_time DESC LIMIT 10;

-- Check index usage
SHOW INDEX FROM riwayat_pelanggaran;

-- Check table size
SELECT 
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.TABLES
WHERE table_schema = 'db_smkn1_kedisiplinan'
ORDER BY (data_length + index_length) DESC;
```

### **Application Logs**:
```bash
# Monitor Laravel logs
tail -f storage/logs/laravel.log

# Monitor queue logs
tail -f storage/logs/worker.log

# Check for errors
grep "ERROR" storage/logs/laravel.log | tail -20
```

---

## 🔄 ROLLBACK PLAN

```bash
# 1. Rollback migrations
php artisan migrate:rollback --step=2

# 2. Restore database (if needed)
mysql -u root -p db_smkn1_kedisiplinan < backup.sql

# 3. Clear cache
php artisan config:clear
php artisan cache:clear

# 4. Restart queue
sudo supervisorctl restart laravel-worker:*
```

---

## 📞 SUPPORT CONTACTS

**Technical Issues**:
- Check documentation in `.kiro/specs/system-optimization-analysis/`
- Review test scenarios in `APPROVAL_LOGIC_TEST_SCENARIOS.md`

**Emergency**:
- Rollback using commands above
- Check Laravel logs for errors
- Verify database state

---

## ✅ SUCCESS INDICATORS

- ✅ Migrations run successfully
- ✅ Indexes created and used
- ✅ Notifications sent for cases with Kepsek
- ✅ No notifications for cases without Kepsek
- ✅ Badge counter updates correctly
- ✅ Approval workflow works
- ✅ No errors in logs

---

**Quick Reference Version**: 1.0  
**Last Updated**: 7 Desember 2025

