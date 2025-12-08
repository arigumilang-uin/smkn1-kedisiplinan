# Project Cleanup Instructions

This document lists old/legacy files that can be safely archived or removed after Clean Architecture refactoring.

---

## ⚠️ IMPORTANT: Before Cleanup

1. **Backup first:**
   ```bash
   git commit -m "Backup before cleanup"
   git branch backup-before-cleanup
   ```

2. **Test thoroughly:**
   - All features working
   - All tests passing
   - No broken routes

3. **Create archive folder:**
   ```bash
   mkdir -p storage/legacy_backup
   ```

---

## 🗑️ Files Safe to Remove/Archive

### Old Controllers (Before Clean Architecture)

These controllers have been replaced by "Clean" versions:

```bash
# Old Siswa Controller
app/Http/Controllers/MasterData/SiswaController.php
# Replaced by: SiswaControllerClean.php

# Old Riwayat Pelanggaran Controller  
app/Http/Controllers/Pelanggaran/RiwayatController.php
# Replaced by: RiwayatPelanggaranControllerClean.php

# Old Tindak Lanjut Controller (if exists)
app/Http/Controllers/TindakLanjut/TindakLanjutController.php
# Replaced by: Clean version

# Old User Controller (if exists)
app/Http/Controllers/UserController.php
# Replaced by: UserService + thin controller
```

**Action:**
```bash
# Option 1: Archive
mkdir -p storage/legacy_backup/controllers
mv app/Http/Controllers/MasterData/SiswaController.php storage/legacy_backup/controllers/

# Option 2: Git remove (if confident)
git rm app/Http/Controllers/MasterData/SiswaController.php
git commit -m "Remove legacy SiswaController (replaced by SiswaControllerClean)"
```

---

### Unused Form Requests

If old controllers had inline validation, clean those up:

```bash
# Check for unused FormRequests
app/Http/Requests/*Request.php
```

**Verify before removing:**
```bash
# Search usage in codebase
grep -r "OldRequest" app/
```

---

### Old Service Files (If Any)

Files that have been refactored or are no longer following Clean Architecture:

```bash
# Example: Mixed concerns services
app/Services/OldPelanggaranService.php  # If exists
app/Services/OldSiswaService.php        # If exists

# Check if these exist and are unused:
find app/Services -name "*Old*" -o -name "*Legacy*"
```

---

### Temporary Files

```bash
# Remove test/debug files
app/Http/Controllers/TestController.php
app/Http/Controllers/DebugController.php

# Remove unused helpers (if any)
app/Helpers/OldHelper.php
```

---

### Migration Duplicates

Check for duplicate or superseded migrations:

```bash
# List all migrations
ls -la database/migrations/

# Remove if you find:
# - Duplicate index creation migrations (keep the idempotent version)
# - Old migration files that were replaced
```

**Example:**
```bash
# If you have both:
2024_01_01_create_siswa_indexes.php        # Old
2025_12_08_add_performance_indexes_to_siswa_table.php  # New (idempotent)

# Keep: New version
# Remove: Old version (if migration never ran in production)
```

---

### Unused Views

If views were tied to old controllers:

```bash
# Check for views not used by new controllers
resources/views/siswa/old_index.blade.php
resources/views/riwayat/old_create.blade.php
```

**Verify usage:**
```bash
grep -r "old_index" app/Http/Controllers/
grep -r "old_create" app/Http/Controllers/
```

---

### Documentation Files

```bash
# Old README (if you have new one)
README.old.md

# Old architecture docs
docs/old_architecture.md
```

---

## ✅ Recommended Cleanup Workflow

### Step 1: Create Backup Branch

```bash
git checkout -b cleanup-legacy-files
git add .
git commit -m "Pre-cleanup checkpoint"
```

### Step 2: Archive to Legacy Folder

```bash
mkdir -p storage/legacy_backup/{controllers,services,views,migrations}

# Example: Archive old controllers
mv app/Http/Controllers/MasterData/SiswaController.php \
   storage/legacy_backup/controllers/SiswaController.old.php

mv app/Http/Controllers/Pelanggaran/RiwayatController.php \
   storage/legacy_backup/controllers/RiwayatController.old.php
```

### Step 3: Update Routes (If Needed)

If routes still reference old controllers:

```php
// routes/web.php
// ❌ Old
use App\Http\Controllers\MasterData\SiswaController;

// ✅ New
use App\Http\Controllers\MasterData\SiswaControllerClean as SiswaController;
```

Or rename the Clean versions:

```bash
# Rename Clean version to standard name
mv app/Http/Controllers/MasterData/SiswaControllerClean.php \
   app/Http/Controllers/MasterData/SiswaController.php

# Update class name inside file
# class SiswaControllerClean → class SiswaController
```

### Step 4: Test Everything

```bash
# Run tests
php artisan test

# Check routes
php artisan route:list

# Test manually
php artisan serve
# Click through all features
```

### Step 5: Commit Cleanup

```bash
git add .
git commit -m "Clean up legacy files after Clean Architecture refactoring

- Removed old SiswaController (replaced by Clean version)
- Removed old RiwayatController (replaced by Clean version)
- Archived legacy files to storage/legacy_backup
- Updated routes to use new controllers
- All tests passing ✅
"
```

---

## 📝 Checklist

Use this checklist to track cleanup progress:

### Controllers
- [ ] Archive old `SiswaController.php`
- [ ] Archive old `RiwayatController.php`
- [ ] Archive old `TindakLanjutController.php` (if exists)
- [ ] Archive old `UserController.php` (if exists)
- [ ] Update routes to reference new controllers
- [ ] Test all controller routes

### Services
- [ ] Remove unused service files
- [ ] Check for duplicate business logic
- [ ] Verify all services follow Clean Architecture

### Form Requests
- [ ] Remove unused FormRequest classes
- [ ] Verify all validation is in FormRequests

### Views
- [ ] Archive unused view files
- [ ] Verify all views render correctly

### Migrations
- [ ] Check for duplicate migrations
- [ ] Keep idempotent versions
- [ ] Test migrations on fresh database

### Routes
- [ ] Update route imports
- [ ] Test all routes
- [ ] Generate route cache: `php artisan route:cache`

### Tests
- [ ] Run full test suite
- [ ] Fix any broken tests
- [ ] Add tests for new features

### Documentation
- [ ] Update README.md
- [ ] Archive old docs
- [ ] Update inline comments

---

## 🚫 DO NOT Remove

These files are still needed:

```bash
# ✅ Keep all files in:
app/Data/              # DTOs
app/Enums/             # Enums
app/Repositories/      # Repositories
app/Services/          # Services
app/Policies/          # Policies
app/Exceptions/        # Custom exceptions
app/Jobs/              # Queue jobs
app/Providers/         # Service providers

# ✅ Keep all Clean versions:
*ControllerClean.php
*Service.php (in Services/)
*Repository.php (in Repositories/)
*Policy.php
```

---

## 🔄 Rollback Plan

If something breaks after cleanup:

### Option 1: Restore from Legacy Backup

```bash
# Copy back from storage/legacy_backup
cp storage/legacy_backup/controllers/SiswaController.old.php \
   app/Http/Controllers/MasterData/SiswaController.php
```

### Option 2: Git Revert

```bash
# Revert cleanup commit
git revert HEAD

# Or checkout backup branch
git checkout backup-before-cleanup
```

### Option 3: Cherry-pick Files

```bash
# Restore specific file from previous commit
git checkout HEAD~1 -- app/Http/Controllers/MasterData/SiswaController.php
```

---

## 📊 Cleanup Impact Report

After cleanup, document the impact:

### Files Removed/Archived

| Type | Before | After | Removed |
|------|--------|-------|---------|
| Controllers | 15 | 10 | 5 |
| Services | 8 | 12 | -4 (added) |
| Views | 50 | 45 | 5 |
| Total LOC | 8000 | 9000 | +1000 (refactored) |

### Benefits

- ✅ Reduced codebase confusion
- ✅ Cleaner git history
- ✅ Easier onboarding for new devs
- ✅ Better adherence to Clean Architecture
- ✅ Improved code maintainability

---

## 🎯 Long-term Maintenance

### Monthly Review

- Check for new legacy patterns
- Refactor as needed
- Update documentation
- Archive old branches

### Quarterly Audit

- Review all controllers (should be thin)
- Review all services (should use repositories)
- Check for direct model usage in controllers
- Verify authorization via policies

---

**Cleanup Completed By:** _____________  
**Date:** _____________  
**Verified By:** _____________  
**Rollback Plan Ready:** ☐ Yes ☐ No

---

## 📞 Questions?

If unsure about removing a file:

1. Check git blame: `git blame <file>`
2. Search usage: `grep -r "ClassName" app/`
3. Ask team in Slack/email
4. When in doubt, **archive instead of delete**

**Remember:** It's easier to delete an archived file than recover a deleted one!
