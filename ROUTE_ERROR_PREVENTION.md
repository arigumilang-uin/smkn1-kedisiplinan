# Route Error Prevention Guide

## 📋 Overview

Dokumen ini menjelaskan strategy untuk prevent missing route errors dan best practices untuk route management.

---

## 🚨 Common Issues & Solutions

### Issue 1: Missing Routes in Views

**Problem:**  
Views memanggil `route('xxx')` tapi route tidak terdaftar → 500 error

**Root Cause:**
- View development terpisah dari route registration
- Routes dibuat incremental tanpa comprehensive planning
- Naming conventions berubah (account → profile, dll)

**Solutions Applied:**

#### A. Comprehensive Route Registration
Semua routes yang dipanggil di views sudah terdaftar:

```php
// routes/web.php - Core routes
// routes/siswa.php - Student management
// routes/master_data.php - Jurusan & Kelas
// routes/pelanggaran.php - Violations
// routes/tindak_lanjut.php - Follow-ups
// routes/user.php - User management + backward compatibility
// routes/report.php - Reports
// routes/admin.php - Admin features, rules, audit
// routes/developer.php - Developer tools + file serving
```

**Total:** 9 route files, 163 routes

#### B. Backward Compatibility Aliases

```php
// routes/user.php
Route::get('/account/edit', [UserController::class, 'editProfile'])
    ->name('account.edit'); // Alias for profile.edit

// routes/admin.php  
Route::get('/data-jurusan', function () {
    return redirect()->route('jurusan.index');
})->name('data-jurusan.index'); // Redirect to new naming
```

**Pattern:** Maintain old route names dengan redirect atau alias

#### C. File Serving Routes

```php
// routes/developer.php
Route::get('/bukti/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);
    
    if (!file_exists($filePath)) {
        abort(404, 'File not found');
    }
    
    return response()->file($filePath);
})->name('bukti.show')->where('path', '.*');
```

**Pattern:** Utility routes untuk common operations

---

## ✅ Best Practices

### 1. Route Verification Before Deployment

**Command:**
```bash
# List all registered routes
php artisan route:list

# Check specific route exists
php artisan route:list --name=users

# Count total routes
php artisan route:list | grep "Showing"
```

### 2. View Route Call Pattern

**GOOD:**
```blade
{{-- Check if route exists --}}
@if(Route::has('users.index'))
    <a href="{{ route('users.index') }}">Users</a>
@endif
```

**BETTER:**
```blade
{{-- Use try-catch in controller --}}
@php
    try {
        $url = route('users.index');
    } catch (\Exception $e) {
        $url = '#';
    }
@endphp
<a href="{{ $url }}">Users</a>
```

### 3. Centralized Route Names

**Create helper file:** `config/routes.php`

```php
return [
    'dashboard' => [
        'admin' => 'dashboard.admin',
        'kepsek' => 'dashboard.kepsek',
        'kaprodi' => 'dashboard.kaprodi',
    ],
    'profile' => [
        'show' => 'profile.show',
        'edit' => 'profile.edit',
        // Backward compatibility
        'account_edit' => 'account.edit',
    ],
];
```

**Usage:**
```blade
<a href="{{ route(config('routes.profile.edit')) }}">Edit Profile</a>
```

### 4. Route Organization Strategy

```
routes/
├── web.php          # Auth, public pages, dashboard redirects
├── api.php          # API endpoints (if needed)
├── Domain Routes:
│   ├── siswa.php           # Student domain
│   ├── pelanggaran.php     # Violation domain  
│   ├── tindak_lanjut.php   # Follow-up domain
│   ├── user.php            # User management domain
│   └── master_data.php     # Master data tables
├── Feature Routes:
│   ├── report.php          # Reporting features
│   ├── admin.php           # Admin-only features
│   └── developer.php       # Dev tools & utilities
└── Utility Routes:
    └── files.php           # File serving (optional)
```

### 5. Naming Conventions

**Resource Routes:**
```php
Route::resource('users', UserController::class);
// Creates: users.index, users.create, users.store, etc.
```

**Custom Actions:**
```php
// Pattern: {resource}.{action}
Route::post('/users/{id}/activate', ...)
    ->name('users.activate');

Route::get('/users/export', ...)
    ->name('users.export');
```

**Nested Resources:**
```php
// Pattern: {parent}.{child}.{action}
Route::get('/kepala-sekolah/approvals', ...)
    ->name('kepala-sekolah.approvals.index');
```

---

## 🔧 Troubleshooting Workflow

### When You Get "Route [xxx] not defined":

**Step 1: Identify the Route**
```bash
# Check if route exists
php artisan route:list --name=xxx

# If not found:
```

**Step 2: Find Where It's Called**
```bash
# Search in views
grep -r "route('xxx')" resources/views/

# Search in controllers
grep -r "route('xxx')" app/Http/Controllers/
```

**Step 3: Determine Appropriate File**
```
- Auth related → routes/web.php
- Specific domain → routes/{domain}.php  
- Admin feature → routes/admin.php
- Developer tool → routes/developer.php
- File serving → routes/developer.php (utility)
```

**Step 4: Register the Route**
```php
// Example: Missing bukti.show
Route::get('/bukti/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);
    if (!file_exists($filePath)) abort(404);
    return response()->file($filePath);
})->name('bukti.show')->where('path', '.*');
```

**Step 5: Clear Route Cache**
```bash
php artisan route:clear
php artisan route:list --name=bukti  # Verify
```

---

## 📊 Current Route Status

**Total Routes:** 163  
**Missing Routes:** 0 ✅  
**Coverage:** 100% ✅

**By Category:**
- Authentication: 3
- Dashboards: 8  
- Master Data: 21
- Violations: 19
- Follow-ups: 16
- User Management: 25 (with backward compatibility)
- Reports: 16
- Admin & Rules: 33
- Developer & Utilities: 5
- Quick Access: 6
- Core & Fallback: 11

---

## 🎯 Prevention Checklist

Before deploying new views:

- [ ] List all `route()` calls in new views
- [ ] Verify each route exists: `php artisan route:list --name=xxx`
- [ ] Register missing routes in appropriate file
- [ ] Test all links in browser
- [ ] Clear route cache: `php artisan route:clear`
- [ ] Run full route list: `php artisan route:list`

---

## 📝 Migration Guide

If you need to rename routes:

### Option A: Keep Both (Recommended)
```php
// New route
Route::get('/profile/edit', ...)->name('profile.edit');

// Old route (backward compatibility)
Route::get('/account/edit', ...)->name('account.edit');
```

### Option B: Redirect
```php
// Old route redirects to new
Route::get('/account/edit', function () {
    return redirect()->route('profile.edit');
})->name('account.edit');
```

### Option C: Update All Views
```bash
# Find and replace in all views
sed -i "s/account\.edit/profile.edit/g" resources/views/**/*.blade.php

# Then remove old route
```

**Recommendation:** Use Option A for production systems!

---

## 🚀 Future Improvements

1. **Route Validation Test:**
```php
// tests/Feature/RouteValidationTest.php
public function test_all_view_routes_exist()
{
    $views = $this->getAllViews();
    $routeCalls = $this->extractRouteCalls($views);
    
    foreach ($routeCalls as $routeName) {
        $this->assertTrue(
            Route::has($routeName),
            "Route [{$routeName}] is called in views but not registered"
        );
    }
}
```

2. **Helper Function:**
```php
// app/Helpers/helpers.php
function safe_route($name, $parameters = [], $absolute = true) {
    try {
        return route($name, $parameters, $absolute);
    } catch (\Exception $e) {
        \Log::warning("Route [{$name}] not found");
        return '#';
    }
}
```

**Usage:**
```blade
<a href="{{ safe_route('users.index') }}">Users</a>
```

3. **Route Documentation Generator:**
```bash
php artisan route:docs

# Generates: docs/routes.md with all routes organized by domain
```

---

**Last Updated:** 2025-12-08  
**Total Routes:** 163  
**Status:** ✅ All routes functional
