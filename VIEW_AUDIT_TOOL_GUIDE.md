# View Routes Audit Tool - Usage Guide

## 📋 Overview

**Command:** `php artisan audit:views`

Custom Artisan command yang scan semua Blade views untuk detect broken route references SEBELUM menyebabkan 500 errors di production.

---

## 🚀 Installation

Command sudah di-create di: `app/Console/Commands/AuditViewRoutes.php`

Laravel akan auto-discover command ini. No additional registration needed!

---

## 📖 Usage

### Basic Audit (Default)

```bash
php artisan audit:views
```

**Output:**
- Summary statistics
- Table of broken routes (file, line, route name)
- Error count

### With Fuzzy Match Suggestions

```bash
php artisan audit:views --suggestions
```

**Output:**
- Broken routes table with suggested fixes
- Similar route names (> 50% similarity)

**Example:**
```
Missing Route:  kasus.edit
Suggestion:     tindak-lanjut.edit
```

### Detailed Mode (Show All Routes)

```bash
php artisan audit:views --detailed
```

**Output:**
- Summary
- Broken routes table
- Valid routes table (first 20)

### Export Results

**JSON Format:**
```bash
php artisan audit:views --export=json
```

**Saved to:** `storage/logs/view-audit-{timestamp}.json`

**CSV Format:**
```bash
php artisan audit:views --export=csv
```

**Saved to:** `storage/logs/view-audit-{timestamp}.csv`

### Combined Options

```bash
php artisan audit:views --suggestions --detailed --export=json
```

---

## 📊 Output Examples

### Summary Table

```
┌─────────────────────────┬───────┐
│ Metric                  │ Count │
├─────────────────────────┼───────┤
│ Blade Files Scanned     │ 70    │
│ Total Route References  │ 245   │
│ Valid Routes            │ 238   │
│ Broken Routes           │ 7     │
│ Registered Routes       │ 163   │
└─────────────────────────┴───────┘
```

### Broken Routes Table

```
┌──────────────────────────┬──────┬─────────────────┬──────────────────────┐
│ File                     │ Line │ Missing Route   │ Suggestion           │
├──────────────────────────┼──────┼─────────────────┼──────────────────────┤
│ siswa/index.blade.php    │ 46   │ siswa.bulk.cre… │ siswa.create         │
│ dashboard/waka.blade.php │ 82   │ kasus.show      │ tindak-lanjut.show   │
│ layouts/app.blade.php    │ 120  │ account.password│ profile.change-pass… │
└──────────────────────────┴──────┴─────────────────┴──────────────────────┘
```

---

## 🔍 What It Detects

### 1. **Route Name Mismatches**

```blade
<!-- View code -->
<a href="{{ route('siswa.bulk.create') }}">Bulk Create</a>

<!-- Actual route name -->
Route::get('/siswa/bulk', ...)->name('siswa.bulk-create'); // Dash instead of dot!
```

**Detected:** ✅ `siswa.bulk.create` not found  
**Suggestion:** `siswa.bulk-create`

### 2. **Renamed Routes**

```blade
<!-- Old view -->
<a href="{{ route('account.edit') }}">Edit Account</a>

<!-- New route -->
Route::get('/profile/edit', ...)->name('profile.edit'); // Renamed!
```

**Detected:** ✅ `account.edit` not found  
**Suggestion:** `profile.edit`

### 3. **Missing Routes**

```blade
<!-- View calls -->
<a href="{{ route('frequency-rules.index') }}">Rules</a>

<!-- But route not registered in any routes/*.php file -->
```

**Detected:** ✅ `frequency-rules.index` not found  
**Suggestion:** (fuzzy match or null)

---

## 🎯 Workflow Integration

### Pre-Deployment Check

```bash
# In CI/CD pipeline or pre-commit hook
php artisan audit:views --export=json

# Check exit code
if [ $? -ne 0 ]; then
    echo "❌ Broken routes found! Fix before deploying."
    exit 1
fi
```

### Local Development

```bash
# After route changes
php artisan route:clear
php artisan audit:views --suggestions

# Fix broken routes
# Re-run to verify
php artisan audit:views
```

### Code Review

```bash
# Export for code review
php artisan audit:views --detailed --export=csv

# Share CSV with team
```

---

## 🔧 How It Works

### 1. **Load Registered Routes**

```php
// Get all route names dari Route::getRoutes()
$registeredRoutes = ['siswa.index', 'siswa.show', ...];
```

### 2. **Scan Blade Files**

```php
// Recursively scan resources/views
$bladeFiles = File::allFiles(resource_path('views'));
```

### 3. **Extract Route References**

```php
// Regex patterns:
route('name')      // Single quotes
route("name")      // Double quotes
@route('name')     // Blade directive (if custom)
```

### 4. **Validate Each Route**

```php
if (Route::has($routeName)) {
    // Valid ✅
} else {
    // Broken ❌ - Find suggestion
}
```

### 5. **Fuzzy Matching**

```php
similar_text($brokenRoute, $registeredRoute, $similarity);
if ($similarity > 50%) {
    // Suggest this route
}
```

---

## 📝 Exit Codes

- **0 (SUCCESS):** No broken routes found ✅
- **1 (FAILURE):** Broken routes detected ❌

**Use in scripts:**
```bash
php artisan audit:views
if [ $? -eq 0 ]; then
    echo "✅ All routes valid"
else
    echo "❌ Fix broken routes first"
fi
```

---

## 🎨 Output Files

### JSON Format

```json
{
  "audit_date": "2025-12-08 13:28:31",
  "summary": {
    "total_files": 70,
    "total_references": 245,
    "valid_routes": 238,
    "broken_routes": 7
  },
  "broken_routes": [
    {
      "file": "siswa/index.blade.php",
      "line": 46,
      "route": "siswa.bulk.create",
      "suggestion": "siswa.create",
      "status": "broken"
    }
  ],
  "valid_routes": [...]
}
```

### CSV Format

```csv
File,Line,Route,Status,Suggestion
siswa/index.blade.php,46,siswa.bulk.create,broken,siswa.create
dashboard/waka.blade.php,82,kasus.show,broken,tindak-lanjut.show
```

---

## 💡 Tips & Best Practices

### 1. **Run After Route Changes**

```bash
# After adding/renaming routes
php artisan route:clear
php artisan audit:views --suggestions
```

### 2. **Use in Git Hooks**

```bash
# .git/hooks/pre-commit
#!/bin/bash
php artisan audit:views
if [ $? -ne 0 ]; then
    echo "Fix broken route references before committing"
    exit 1
fi
```

### 3. **Regular Audits**

```bash
# Weekly cron job
0 9 * * 1 php artisan audit:views --export=json --suggestions
```

### 4. **Fix Priority**

1. Fix routes called from **authentication** flows first
2. Then **dashboard** routes
3. Then **CRUD** operations routes
4. Finally **report** routes

---

## 🐛 Troubleshooting

### Command Not Found

```bash
# Clear command cache
php artisan clear-compiled
composer dump-autoload

# Verify command exists
php artisan list | grep audit
```

### No Routes Loaded

```bash
# Clear route cache first
php artisan route:clear

# Then run audit
php artisan audit:views
```

### False Positives

Some dynamic routes might show as "broken" but work at runtime:

```blade
<!-- Dynamic route name -->
<a href="{{ route($dynamicRouteName) }}">
```

**Solution:** Tool can't validate dynamic names. Review manually.

---

## 🎯 Expected Output (This Project)

Based on earlier fixes, you should see:

**Broken Routes (0-5 expected):**
- Possibly some legacy route names in old views
- Might find routes in components not yet updated

**Valid Routes (238+ expected):**
- All main CRUD routes
- Dashboard routes
- Profile routes
- Master data routes
- Report routes

---

## 🚀 Next Steps After Audit

1. **Review broken routes list**
2. **Check suggestions** (usually correct!)
3. **Update views** with correct route names
4. **Re-run audit** to verify
5. **Export clean results** for documentation

---

## 📚 Related Commands

```bash
# View all routes
php artisan route:list

# View specific routes
php artisan route:list --name=siswa

# Clear route cache
php artisan route:clear

# Cache routes (production)
php artisan route:cache
```

---

**Created:** 2025-12-08  
**For:** SMKN 1 Kedisiplinan Project  
**Command:** `audit:views`  
**Location:** `app/Console/Commands/AuditViewRoutes.php`
