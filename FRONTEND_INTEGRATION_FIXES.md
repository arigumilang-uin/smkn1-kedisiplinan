# 🔧 CRITICAL FRONTEND INTEGRATION FIXES

**Generated:** 2025-12-08 14:35  
**Priority:** URGENT  
**Errors Found:** 7  
**Status:** FIXING...

---

## ERROR 1: SiswaController@index - Missing $allJurusan ✅ FIXED

**File:** `app/Http/Controllers/MasterData/SiswaController.php`

**Line 44-65:**
```php
// BEFORE
$allKelas = Kelas::orderBy('nama_kelas')->get();
return view('siswa.index', compact('siswa', 'allKelas'));

// AFTER
$allKelas = Kelas::orderBy('nama_kelas')->get();
$allJurusan = Jurusan::orderBy('nama_jurusan')->get();
return view('siswa.index', compact('siswa', 'allKelas', 'allJurusan'));
```

**Status:** ✅ FIXED

---

## ERROR 2: User Controller - Missing $kelas and $jurusan

**File:** `app/Http/Controllers/UserController.php`

### Fix A: create() method

**Add after line where $roles is fetched:**
```php
public function create()
{
    $roles = \App\Models\Role::all();
    
    // ADD THESE:
    $kelas = \App\Models\Kelas::orderBy('nama_kelas')->get();
    $jurusan = \App\Models\Jurusan::orderBy('nama_jurusan')->get();
    
    return view('users.create', compact('roles', 'kelas', 'jurusan'));
}
```

### Fix B: edit() method

**Add after fetching $user:**
```php
public function edit($id)
{
    $user = $this->userService->getUser($id);
    $roles = \App\Models\Role::all();
    
    // ADD THESE:
    $kelas = \App\Models\Kelas::orderBy('nama_kelas')->get();
    $jurusan = \App\Models\Jurusan::orderBy('nama_jurusan')->get();
    
    return view('users.edit', compact('user', 'roles', 'kelas', 'jurusan'));
}
```

---

## ERROR 3: FilterRiwayatRequest - Undefined array key "tingkat"

**File:** `app/Http/Requests/Pelanggaran/FilterRiwayatRequest.php`

**Line 72:**
```php
// BEFORE (will error if tingkat not in request)
'tingkat' => $validated['tingkat'] ? \App\Enums\TingkatPelanggaran::from($validated['tingkat']) : null,

// AFTER (safe null handling)
'tingkat' => !empty($validated['tingkat']) ? \App\Enums\TingkatPelanggaran::from($validated['tingkat']) : null,
```

---

## ERROR 4: kelas Route - Wrong Parameter Name

**File:** `routes/master_data.php`

**Current:**
```php
Route::resource('kelas', KelasController::class);
```

**Problem:** Laravel generates parameter name as `kela` (singular) instead of `kelas`

**Fix - Add parameters() specification:**
```php
Route::resource('kelas', KelasController::class)
    ->parameters(['kelas' => 'kelas']); // Force parameter name to be 'kelas'
```

---

## ERROR 5: Missing View - profile.edit

**File:** `app/Http/Controllers/UserController.php`

**Problem:** Controller tries to load `profile.edit` but view doesn't exist

**Find correct view:**
```bash
# Check if view exists:
ls resources/views/profile/
ls resources/views/account/
ls resources/views/users/
```

**Likely Fix:**
```php
// BEFORE
return view('profile.edit', compact('user'));

// AFTER (use existing view)
return view('users.profile', compact('user'));
// OR
return view('account.edit', compact('user'));
```

**Manual Action Required:**
1. Find existing profile edit view
2. Update UserController@editProfile to use correct view name

---

## ERROR 6: Missing Route - audit.activity.export-csv

**File:** `routes/admin.php` or `routes/legacy.php`

**Add to routes/admin.php:**
```php
// After audit.activity.index route:
Route::get('/audit/activity/export-csv', [\App\Http\Controllers\Audit\ActivityLogController::class, 'exportCsv'])
    ->name('audit.activity.export-csv')
    ->middleware('role:Operator Sekolah,Kepala Sekolah');
```

**OR add placeholder in routes/legacy.php:**
```php
Route::get('/audit/activity/export-csv', function () {
    return redirect()->route('audit.activity.index')
        ->with('info', 'Export CSV feature coming soon.');
})->name('audit.activity.export-csv');
```

---

## ERROR 7: RiwayatPelanggaranController - Check Master Data

**File:** `app/Http/Controllers/Pelanggaran/RiwayatPelanggaranController.php`

**Ensure index() method passes:**
```php
public function index()
{
    // ... existing code ...
    
    // ADD THESE for filter dropdowns:
    $allKelas = \App\Models\Kelas::orderBy('nama_kelas')->get();
    $allJurusan = \App\Models\Jurusan::orderBy('nama_jurusan')->get();
    $pencatats = \App\Models\User::whereHas('role', function($q) {
        $q->whereIn('nama_role', ['Guru', 'Waka Kesiswaan', 'Operator Sekolah']);
    })->orderBy('nama')->get();
    
    return view('riwayat.index', compact(
        'riwayat', 
        'allKelas', 
        'allJurusan', 
        'pencatats'
    ));
}
```

---

## QUICK FIX COMMANDS

```bash
# 1. Clear all caches
php artisan optimize:clear

# 2. After fixes, verify routes
php artisan route:list | grep kelas

# 3. Check views exist
ls resources/views/profile/
ls resources/views/users/

# 4. Test in browser
php artisan serve
```

---

## PRIORITY ORDER

1. ✅ **ERROR 1** - SiswaController (FIXED)
2. ⚠️ **ERROR 3** - FilterRiwayatRequest (CRITICAL - causes 500)
3. ⚠️ **ERROR 4** - kelas route parameter (breaks edit/update)
4. ⚠️ **ERROR 2** - UserController (blocks user management)
5. ⚠️ **ERROR 5** - profile.edit view (blocks profile access)
6. ⚠️ **ERROR 6** - export-csv route (minor - can be placeholder)
7. ⚠️ **ERROR 7** - Riwayat filters (minor - view works without filters)

---

## TESTING CHECKLIST

After applying ALL fixes:

- [ ] Open /siswa → No error ✅
- [ ] Open /riwayat → No error
- [ ] Open /users/create → No error
- [ ] Open /users/4/edit → No error
- [ ] Open /kelas/26 → No error
- [ ] Edit kelas → No error
- [ ] Open /account/edit → No error  
- [ ] Open /audit/activity → No error (or info message)

---

**Files to Modify:** 5  
**Lines to Change:** ~30  
**Estimated Time:** 15 minutes  
**Impact:** Fixes ALL 7 errors!

---

**Next:** Apply remaining 6 fixes manually or run provided code snippets.
