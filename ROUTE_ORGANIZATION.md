# Route Organization Documentation

## 📋 Overview

Routes have been reorganized from monolithic `web.php` into domain-specific files following Clean Architecture principles.

---

## 🗂️ Route File Structure

```
routes/
├── web.php              # Core routes (Dashboard, Reports, Settings)
├── siswa.php            # Siswa CRUD & operations
├── pelanggaran.php      # Riwayat Pelanggaran & Jenis Pelanggaran
├── tindak_lanjut.php    # Tindak Lanjut & approval workflow
└── user.php             # User management & profile
```

---

## 📝 Route Files Detail

### 1. **web.php** (Core Routes)

**Purpose:** Essential application routes that don't belong to specific domains

**Routes:**
- `/` - Landing page (guest)
- `/dashboard` - Main dashboard (auth)
- `/quick/*` - Quick access shortcuts
- `/reports/*` - Reports & analytics
- `/settings/*` - Application settings

**Middleware:**
- Guest routes: `guest`
- Authenticated routes: `auth`, `verified`
- Admin routes: `role:Operator Sekolah`

---

### 2. **siswa.php** (Siswa Module)

**Purpose:** Student management routes

**Main Routes:**
```
GET     /siswa              → siswa.index
GET     /siswa/create       → siswa.create
POST    /siswa              → siswa.store
GET     /siswa/{id}         → siswa.show
GET     /siswa/{id}/edit    → siswa.edit
PUT     /siswa/{id}         → siswa.update
DELETE  /siswa/{id}         → siswa.destroy
```

**Additional Routes:**
- `/siswa/export` - Export to Excel
- `/siswa/import` - Import from Excel (form & process)
- `/siswa/bulk-delete` - Bulk deletion
- `/siswa/statistics` - Statistics overview

**Controller:** `SiswaControllerClean`

**Middleware:**
- All: `auth`, `verified`
- Import/Export: `can:bulkImport,App\Models\Siswa`

**Authorization:**
- View: All authenticated users (scoped by role)
- Create: Operator Sekolah (via Policy)
- Update: Operator Sekolah, Wali Kelas (limited fields)
- Delete: Operator Sekolah only

---

### 3. **pelanggaran.php** (Pelanggaran Module)

**Purpose:** Violation records & violation types management

#### **Section A: Riwayat Pelanggaran**

**Main Routes:**
```
GET     /riwayat              → riwayat.index
GET     /riwayat/create       → riwayat.create
POST    /riwayat              → riwayat.store
GET     /riwayat/{id}         → riwayat.show
GET     /riwayat/{id}/edit    → riwayat.edit
PUT     /riwayat/{id}         → riwayat.update
DELETE  /riwayat/{id}         → riwayat.destroy
```

**Additional Routes:**
- `/riwayat/my` - My recorded violations
- `/riwayat/export` - Export violations
- `/riwayat/siswa/{id}/statistics` - Statistics per student

**Controller:** `RiwayatPelanggaranControllerClean`

**Authorization:**
- Create: All teacher roles (via `isTeacher()`)
- Update/Delete: 
  - Operator: Full access
  - Others: Own records only (max 3 days)

#### **Section B: Jenis Pelanggaran (Master Data)**

**Main Routes:**
```
GET     /jenis-pelanggaran              → jenis-pelanggaran.index
GET     /jenis-pelanggaran/create       → jenis-pelanggaran.create
POST    /jenis-pelanggaran              → jenis-pelanggaran.store
GET     /jenis-pelanggaran/{id}         → jenis-pelanggaran.show
GET     /jenis-pelanggaran/{id}/edit    → jenis-pelanggaran.edit
PUT     /jenis-pelanggaran/{id}         → jenis-pelanggaran.update
DELETE  /jenis-pelanggaran/{id}         → jenis-pelanggaran.destroy
```

**Additional Routes:**
- `/jenis-pelanggaran/{id}/toggle-active` - Activate/deactivate
- `/jenis-pelanggaran/import` - Bulk import

**Controller:** `JenisPelanggaranController`

**Middleware:** `role:Operator Sekolah` (all routes)

---

### 4. **tindak_lanjut.php** (Tindak Lanjut Module)

**Purpose:** Follow-up actions management & approval workflow

#### **Section A: CRUD Routes**

**Main Routes:**
```
GET     /tindak-lanjut              → tindak-lanjut.index
GET     /tindak-lanjut/create       → tindak-lanjut.create
POST    /tindak-lanjut              → tindak-lanjut.store
GET     /tindak-lanjut/{id}         → tindak-lanjut.show
GET     /tindak-lanjut/{id}/edit    → tindak-lanjut.edit
PUT     /tindak-lanjut/{id}         → tindak-lanjut.update
DELETE  /tindak-lanjut/{id}         → tindak-lanjut.destroy
```

#### **Section B: Approval Workflow**

**Workflow Routes:**
- `POST /tindak-lanjut/{id}/approve` - Approve action
- `POST /tindak-lanjut/{id}/reject` - Reject action
- `POST /tindak-lanjut/{id}/complete` - Complete/close action

**List Routes:**
- `/tindak-lanjut/pending-approval` - Pending list (for approvers)
- `/tindak-lanjut/my-approvals` - My approval history
- `/tindak-lanjut/statistics` - Statistics overview

**Middleware:**
- Approve/Reject/Complete: `can:action,App\Models\TindakLanjut`
- Pending list: `role:Kepala Sekolah,Waka Kesiswaan,Kaprodi`

#### **Section C: Surat Panggilan**

**Surat Routes:**
- `GET /surat-panggilan/{id}` - Print surat
- `GET /surat-panggilan/{id}/pdf` - Download PDF
- `POST /surat-panggilan/{id}/send-email` - Send via email

**Controller:** `TindakLanjutController`

---

### 5. **user.php** (User Management)

**Purpose:** User administration & profile management

#### **Section A: User CRUD (Admin Only)**

**Main Routes:**
```
GET     /users              → users.index
GET     /users/create       → users.create
POST    /users              → users.store
GET     /users/{id}         → users.show
GET     /users/{id}/edit    → users.edit
PUT     /users/{id}         → users.update
DELETE  /users/{id}         → users.destroy
```

**Management Routes:**
- `GET /users/{id}/reset-password` - Reset password form
- `POST /users/{id}/reset-password` - Reset password action
- `POST /users/{id}/toggle-activation` - Activate/deactivate user
- `POST /users/bulk-activate` - Bulk activation
- `POST /users/bulk-deactivate` - Bulk deactivation
- `POST /users/bulk-delete` - Bulk deletion
- `GET /users/export` - Export users
- `POST /users/import` - Import users

**Middleware:** `role:Operator Sekolah,Kepala Sekolah` (all routes)

#### **Section B: Profile (All Users)**

**Profile Routes:**
- `GET /profile` - View own profile
- `GET /profile/edit` - Edit profile form
- `PUT /profile` - Update profile
- `GET /profile/change-password` - Change password form
- `POST /profile/change-password` - Change password action

**Middleware:** `auth`, `verified` (all authenticated users)

**Controller:** `UserController`

---

## 🔧 Configuration

### Route Registration (bootstrap/app.php)

```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
    then: function () {
        // Domain-specific routes (all under 'web' middleware group)
        Route::middleware('web')
            ->group(base_path('routes/siswa.php'));

        Route::middleware('web')
            ->group(base_path('routes/pelanggaran.php'));

        Route::middleware('web')
            ->group(base_path('routes/tindak_lanjut.php'));

        Route::middleware('web')
            ->group(base_path('routes/user.php'));
    },
)
```

---

## 🛡️ Middleware Stack

### Global Middleware (All Routes)

```
web → [
    EncryptCookies,
    AddQueuedCookiesToResponse,
    StartSession,
    ShareErrorsFromSession,
    VerifyCsrfToken,
    SubstituteBindings,
    CheckAccountActive  // Custom
]
```

### Route-Specific Middleware

| Middleware | Purpose | Applied To |
|------------|---------|------------|
| `guest` | Guest only | Login, Register |
| `auth` | Authenticated only | All protected routes |
| `verified` | Email verified | Protected routes |
| `role:*` | Specific role | Admin functions |
| `can:*` | Policy check | Authorization-based routes |

---

## 🎯 URL Structure

### Pattern Convention

```
/{resource}                       → index (list)
/{resource}/create                → create form
/{resource}                       → store (POST)
/{resource}/{id}                  → show (detail)
/{resource}/{id}/edit             → edit form
/{resource}/{id}                  → update (PUT)
/{resource}/{id}                  → destroy (DELETE)

/{resource}/{action}              → custom actions
/{resource}/{id}/{action}         → custom actions on resource
```

### Examples

```
GET  /siswa                       → List all students
POST /siswa                       → Create student
GET  /siswa/123                   → View student #123
PUT  /siswa/123                   → Update student #123
GET  /siswa/export                → Export all students
POST /tindak-lanjut/456/approve   → Approve action #456
```

---

## 📊 Route Count by Module

| Module | Resource Routes | Custom Routes | Total |
|--------|----------------|---------------|-------|
| Core (web.php) | 0 | ~10 | 10 |
| Siswa | 7 | 5 | 12 |
| Pelanggaran | 14 (2 resources) | 5 | 19 |
| Tindak Lanjut | 7 | 9 | 16 |
| User | 7 | 11 | 18 |
| **TOTAL** | **35** | **40** | **75** |

---

## 🔍 Route Testing

### List All Routes

```bash
php artisan route:list
```

### Filter Routes by Name

```bash
php artisan route:list --name=siswa
php artisan route:list --name=riwayat
php artisan route:list --name=tindak-lanjut
```

### Filter Routes by Method

```bash
php artisan route:list --method=POST
php artisan route:list --method=GET
```

### Clear Route Cache

```bash
php artisan route:clear
php artisan route:cache  # For production
```

---

## 🎨 Benefits of This Organization

### 1. **Maintainability**
- Easy to find routes by domain
- Clear separation of concerns
- Reduced file size per route file

### 2. **Team Collaboration**
- Avoid merge conflicts
- Multiple developers can work on different modules
- Clear ownership per domain

### 3. **Scalability**
- Easy to add new modules
- Can split further if needed (e.g., api routes)
- Modular architecture

### 4. **Documentation**
- Self-documenting structure
- Route grouping visible in file structure
- Easy to generate API docs

---

## 🚀 Adding New Route File

### Step 1: Create Route File

```bash
touch routes/new_module.php
```

### Step 2: Add Routes

```php
<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NewModuleController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('new-module', NewModuleController::class);
});
```

### Step 3: Register in bootstrap/app.php

```php
then: function () {
    // ... existing routes ...
    
    Route::middleware('web')
        ->group(base_path('routes/new_module.php'));
}
```

### Step 4: Test

```bash
php artisan route:list --name=new-module
```

---

## 🐛 Troubleshooting

### Routes Not Working

**Issue:** 404 on domain routes  
**Solution:** Clear route cache
```bash
php artisan route:clear
```

**Issue:** Middleware not applying  
**Solution:** Check middleware in route file and bootstrap/app.php

**Issue:** Controller not found  
**Solution:** Check namespace import in route file
```php
use App\Http\Controllers\YourController;
```

---

## 📖 Best Practices

### DO's ✅

- Group related routes together
- Use route names for all routes
- Apply middleware at group level
- Use resource routes for CRUD
- Use meaningful route names

### DON'Ts ❌

- Don't put routes in random files
- Don't duplicate route names
- Don't skip middleware
- Don't use closure for complex logic
- Don't hardcode URLs in code (use route())

---

**Last Updated:** December 8, 2024  
**Laravel Version:** 11.x
