# 🚀 COMPLETE ALL REMAINING FIXES - EXECUTION GUIDE

**Date:** 2025-12-08  
**Remaining Fixes:** 4  
**Priority:** COMPLETE NOW

---

## ⚠️ IMPORTANT: UserController.php Got Corrupted!

**Action Required:** Restore UserController.php from backup or git, then apply fixes below.

```bash
# Restore from git (if available)
git checkout app/Http/Controllers/UserController.php

# OR manually fix the corrupted sections
```

---

## FIX 4 & 5: UserController - Add Master Data ✅

**File:** `app/Http/Controllers/UserController.php`

### Fix A: create() method (around line 61-65)

```php
/**
 * Show create user form.
 */
public function create(): View
{
    $roles = Role::all();
    $kelas = \App\Models\Kelas::orderBy('nama_kelas')->get();
    $jurusan = \App\Models\Jurusan::orderBy('nama_jurusan')->get();
    
    return view('users.create', compact('roles', 'kelas', 'jurusan'));
}
```

### Fix B: edit() method (around line 93-99)

```php
/**
 * Show edit user form.
 */
public function edit(int $id): View
{
    $user = $this->userService->getUser($id);
    $roles = Role::all();
    $kelas = \App\Models\Kelas::orderBy('nama_kelas')->get();
    $jurusan = \App\Models\Jurusan::orderBy('nama_jurusan')->get();
    
    return view('users.edit', compact('user', 'roles', 'kelas', 'jurusan'));
}
```

**Test:** `/users/create` and `/users/4/edit` should work without errors

---

## FIX 6: UserController - Fix Profile View ✅

**File:** `app/Http/Controllers/UserController.php`

### Analysis:
- No `resources/views/profile/` directory exists
- `resources/views/users/edit.blade.php` EXISTS
- Profile editing likely uses user's own detail view

### Fix: editProfile() method (around line 176-179)

**Option A - Use users.edit view with flag:**
```php
/**
 * Show edit own profile form.
 */
public function editProfile(): View
{
    $user = $this->userService->getUser(auth()->id());
    $roles = Role::all();
    $kelas = \App\Models\Kelas::orderBy('nama_kelas')->get();
    $jurusan = \App\Models\Jurusan::orderBy('nama_jurusan')->get();
    $isOwnProfile = true; // Flag for view
    
    return view('users.edit', compact('user', 'roles', 'kelas', 'jurusan', 'isOwnProfile'));
}
```

**OR Option B - Create simple profile view:**

Create file: `resources/views/users/profile.blade.php`
```blade
@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="container">
    <h2>Edit Profile</h2>
    <form action="{{ route('profile.update') }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label>Nama</label>
            <input type="text" name="nama" class="form-control" value="{{ old('nama', $user->nama) }}" required>
        </div>
        
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
        </div>
        
        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
        </div>
        
        <button type="submit" class="btn btn-primary">Update Profile</button>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
```

Then update controller:
```php
public function editProfile(): View
{
    $user = $this->userService->getUser(auth()->id());
    return view('users.profile', compact('user'));
}
```

**RECOMMENDED:** Use Option B (Create simple view)

**Test:** `/account/edit` should load properly

---

## FIX 7: Missing Route - audit.activity.export-csv ✅

**File:** `routes/legacy.php`

**Add at the end, before closing }):**

```php
// ===================================================================
// LEGACY: AUDIT EXPORT ROUTE
// ===================================================================

/**
 * Audit activity export CSV
 * Placeholder until export feature is implemented
 */
Route::get('/audit/activity/export-csv', function () {
    return redirect()->route('audit.activity.index')
        ->with('info', 'Export CSV feature coming soon. Contact IT for manual export.');
})->name('audit.activity.export-csv')
  ->middleware('role:Operator Sekolah,Kepala Sekolah');
```

**Test:** `/audit/activity` should load without RouteNotFoundException

---

## EXECUTION SEQUENCE

### Step 1: Verify/Restore UserController

```bash
# Check if file is corrupted
cat app/Http/Controllers/UserController.php | head -100

# If corrupted, restore:
git checkout app/Http/Controllers/UserController.php
```

### Step 2: Apply UserController Fixes

**Manual edit required:**
1. Open `app/Http/Controllers/UserController.php`
2. Find `create()` method → Add `$kelas` and `$jurusan`
3. Find `edit()` method → Add `$kelas` and `$jurusan`
4. Find `editProfile()` method → Create simple view OR use users.edit

### Step 3: Create Profile View (if using Option B)

```bash
# Create new view file
touch resources/views/users/profile.blade.php
```

Then copy the blade template from FIX 6 Option B above.

### Step 4: Add Export CSV Route

**Edit:** `routes/legacy.php`  
**Add:** The route definition from FIX 7 above (before closing `}`).

### Step 5: Clear Caches

```bash
php artisan route:clear
php artisan view:clear
```

### Step 6: Test ALL Endpoints

```bash
# Open in browser:
http://localhost:8000/siswa          # ✅ Should work
http://localhost:8000/riwayat        # ✅ Should work
http://localhost:8000/users/create   # ⚠️ Test this
http://localhost:8000/users/4/edit   # ⚠️ Test this
http://localhost:8000/account/edit   # ⚠️ Test this
http://localhost:8000/audit/activity # ⚠️ Test this
```

---

## QUICK FIX SCRIPT (Copy-Paste)

**For routes/legacy.php (add before closing }):**
```php
Route::get('/audit/activity/export-csv', function () {
    return redirect()->route('audit.activity.index')
        ->with('info', 'Export CSV coming soon.');
})->name('audit.activity.export-csv');
```

**For UserController.php create() method:**
```php
$kelas = \App\Models\Kelas::orderBy('nama_kelas')->get();
$jurusan = \App\Models\Jurusan::orderBy('nama_jurusan')->get();
return view('users.create', compact('roles', 'kelas', 'jurusan'));
```

**For UserController.php edit() method:**
```php
$kelas = \App\Models\Kelas::orderBy('nama_kelas')->get();
$jurusan = \App\Models\Jurusan::orderBy('nama_jurusan')->get();
return view('users.edit', compact('user', 'roles', 'kelas', 'jurusan'));
```

---

## VERIFICATION CHECKLIST

After completing all fixes:

- [ ]  UserController.php not corrupted
- [ ] `create()` method has $kelas, $jurusan
- [ ] `edit()` method has $kelas, $jurusan
- [ ] `editProfile()` uses correct view
- [ ] Profile view exists (users/profile.blade.php OR uses users/edit)
- [ ] audit.activity.export-csv route added to legacy.php
- [ ] Routes cleared
- [ ] All 7 endpoints tested

---

## SUCCESS CRITERIA

**ALL these must work without 500 errors:**

1. ✅ /siswa - Loads with filters
2. ✅ /riwayat - Loads without undefined tingkat error
3. ✅ /kelas/26 - Detail loads
4. ⚠️ /users/create - Form loads with all dropdowns
5. ⚠️ /users/4/edit - Form loads with all dropdowns
6. ⚠️ /account/edit - Profile edit loads
7. ⚠️ /audit/activity - Loads (export button shows info message)

---

**Total Fixes:** 7  
**Applied Automatically:** 3  
**Need Manual Application:** 4  
**Estimated Time:** 10-15 minutes  

**ACTION:** Apply the 4 manual fixes above then test!
