# Backend Refactoring - Implementation Plan

## Overview
Rencana implementasi detail untuk refactoring backend sistem kedisiplinan SMKN 1 menjadi struktur yang clean, konsisten, dan mudah dipahami.

---

## PHASE 1: FOLDER RESTRUCTURING (Priority: CRITICAL)

### 1.1 Create New Folder Structure

#### Action Items:
```bash
# Create new controller folders
mkdir app/Http/Controllers/MasterData
mkdir app/Http/Controllers/Pelanggaran
mkdir app/Http/Controllers/Rules
mkdir app/Http/Controllers/KepalaSekolah
mkdir app/Http/Controllers/Data
mkdir app/Http/Controllers/Audit
mkdir app/Http/Controllers/User
mkdir app/Http/Controllers/Utility

# Create new service folders
mkdir app/Services/Pelanggaran
mkdir app/Services/Rules
mkdir app/Services/User
mkdir app/Services/Report
mkdir app/Services/Statistics
mkdir app/Services/Audit

# Create traits folder
mkdir app/Traits
```

---

### 1.2 Move Controllers to Appropriate Folders

#### 1.2.1 MasterData Controllers
**Files to Move**:
```
app/Http/Controllers/JurusanController.php
  → app/Http/Controllers/MasterData/JurusanController.php

app/Http/Controllers/KelasController.php
  → app/Http/Controllers/MasterData/KelasController.php

app/Http/Controllers/SiswaController.php
  → app/Http/Controllers/MasterData/SiswaController.php

app/Http/Controllers/JenisPelanggaranController.php
  → app/Http/Controllers/MasterData/JenisPelanggaranController.php
```

**Namespace Update**:
```php
// OLD
namespace App\Http\Controllers;

// NEW
namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
```

**Routes Update** (routes/web.php):
```php
// OLD
use App\Http\Controllers\JurusanController;

// NEW
use App\Http\Controllers\MasterData\JurusanController;
```

---

#### 1.2.2 Pelanggaran Controllers
**Files to Move**:
```
app/Http/Controllers/PelanggaranController.php
  → app/Http/Controllers/Pelanggaran/PelanggaranController.php

app/Http/Controllers/RiwayatController.php
  → app/Http/Controllers/Pelanggaran/RiwayatController.php

app/Http/Controllers/MyRiwayatController.php
  → app/Http/Controllers/Pelanggaran/MyRiwayatController.php

app/Http/Controllers/TindakLanjutController.php
  → app/Http/Controllers/Pelanggaran/TindakLanjutController.php
```

**Namespace Update**: Same pattern as above

---

#### 1.2.3 Rules Controllers
**Files to Move**:
```
app/Http/Controllers/FrequencyRulesController.php
  → app/Http/Controllers/Rules/FrequencyRulesController.php

app/Http/Controllers/PembinaanInternalRulesController.php
  → app/Http/Controllers/Rules/PembinaanInternalRulesController.php

app/Http/Controllers/RulesEngineSettingsController.php
  → app/Http/Controllers/Rules/RulesEngineSettingsController.php
```

---

#### 1.2.4 KepalaSekolah Controllers
**Files to Move**:
```
app/Http/Controllers/Dashboard/ApprovalController.php
  → app/Http/Controllers/KepalaSekolah/ApprovalController.php

app/Http/Controllers/Dashboard/ReportController.php
  → app/Http/Controllers/KepalaSekolah/ReportController.php

app/Http/Controllers/Dashboard/SiswaPerluPembinaanController.php
  → app/Http/Controllers/KepalaSekolah/SiswaPerluPembinaanController.php
```

**Namespace Update**:
```php
// OLD
namespace App\Http\Controllers\Dashboard;

// NEW
namespace App\Http\Controllers\KepalaSekolah;
```

---

#### 1.2.5 Data Controllers (Read-only with Stats)
**Files to Move**:
```
app/Http/Controllers/DataJurusanController.php
  → app/Http/Controllers/Data/DataJurusanController.php

app/Http/Controllers/DataKelasController.php
  → app/Http/Controllers/Data/DataKelasController.php
```

---

#### 1.2.6 Audit Controllers
**Files to Move**:
```
app/Http/Controllers/Dashboard/ActivityLogController.php
  → app/Http/Controllers/Audit/ActivityLogController.php

app/Http/Controllers/AuditController.php
  → DELETE (merge functionality into ActivityLogController if needed)
```

---

#### 1.2.7 User Controllers
**Files to Move**:
```
app/Http/Controllers/UserController.php
  → app/Http/Controllers/User/UserController.php

app/Http/Controllers/ProfileController.php
  → app/Http/Controllers/User/ProfileController.php

app/Http/Controllers/Dashboard/UserManagementController.php
  → DELETE (duplicate of UserController)
```

---

#### 1.2.8 Utility Controllers
**Files to Move**:
```
app/Http/Controllers/FileController.php
  → app/Http/Controllers/Utility/FileController.php

app/Http/Controllers/DeveloperController.php
  → app/Http/Controllers/Utility/DeveloperController.php
```

---

### 1.3 Final Dashboard Folder Structure

**After cleanup, Dashboard/ should only contain**:
```
app/Http/Controllers/Dashboard/
├── AdminDashboardController.php
├── DeveloperDashboardController.php
├── KaprodiDashboardController.php
├── KepsekDashboardController.php
├── WakaSaranaDashboardController.php
├── WaliKelasDashboardController.php
└── WaliMuridDashboardController.php
```

---

## PHASE 2: SERVICE LAYER ENHANCEMENT

### 2.1 Move Existing Services to Subfolders

#### 2.1.1 Pelanggaran Services
```
app/Services/PelanggaranRulesEngine.php
  → app/Services/Pelanggaran/PelanggaranRulesEngine.php

app/Services/SuratPanggilanService.php
  → app/Services/Pelanggaran/SuratPanggilanService.php
```

**Namespace Update**:
```php
// OLD
namespace App\Services;

// NEW
namespace App\Services\Pelanggaran;
```

---

#### 2.1.2 Rules Services
```
app/Services/RulesEngineSettingsService.php
  → app/Services/Rules/RulesEngineSettingsService.php
```

---

#### 2.1.3 User Services
```
app/Services/RoleService.php
  → app/Services/User/RoleService.php

app/Services/UserNamingService.php
  → app/Services/User/UserNamingService.php
```

---

### 2.2 Create New Services

#### 2.2.1 ReportService
**File**: `app/Services/Report/ReportService.php`

**Purpose**: Centralize report generation logic

**Methods**:
```php
class ReportService
{
    public function generatePelanggaranReport(array $filters): Collection
    public function generateSiswaReport(array $filters): Collection
    public function generateStatisticsReport(string $period): array
    public function exportToCsv(Collection $data, string $filename): string
    public function exportToPdf(Collection $data, string $template): string
}
```

**Extract from**:
- `ReportController` (current report generation logic)
- Dashboard controllers (export logic)

---

#### 2.2.2 StatisticsService
**File**: `app/Services/Statistics/StatisticsService.php`

**Purpose**: Centralize statistics calculation

**Methods**:
```php
class StatisticsService
{
    public function getDashboardStats(User $user): array
    public function getPelanggaranStats(array $filters): array
    public function getSiswaStats(int $siswaId): array
    public function getJurusanStats(int $jurusanId): array
    public function getKelasStats(int $kelasId): array
    public function getTopViolators(int $limit = 10): Collection
    public function getViolationTrends(string $period): array
}
```

**Extract from**:
- All dashboard controllers (duplicated stats logic)
- `DataJurusanController` (stats calculation)
- `DataKelasController` (stats calculation)

---

#### 2.2.3 AuditService
**File**: `app/Services/Audit/AuditService.php`

**Purpose**: Centralize audit logging

**Methods**:
```php
class AuditService
{
    public function logActivity(string $action, string $description, ?Model $model = null): void
    public function getActivityLog(array $filters): Collection
    public function getUserLoginHistory(int $userId): Collection
    public function getModelHistory(Model $model): Collection
}
```

---

### 2.3 Service Interfaces (Optional but Recommended)

#### Create Contracts
```
app/Contracts/
├── ReportServiceInterface.php
├── StatisticsServiceInterface.php
└── AuditServiceInterface.php
```

**Example**:
```php
namespace App\Contracts;

interface StatisticsServiceInterface
{
    public function getDashboardStats(User $user): array;
    public function getPelanggaranStats(array $filters): array;
    // ... other methods
}
```

**Bind in AppServiceProvider**:
```php
$this->app->bind(
    StatisticsServiceInterface::class,
    StatisticsService::class
);
```

---

## PHASE 3: EXTRACT COMMON LOGIC TO TRAITS

### 3.1 HasFilters Trait
**File**: `app/Traits/HasFilters.php`

**Purpose**: Common filtering logic for controllers

**Methods**:
```php
trait HasFilters
{
    protected function applyFilters($query, array $filters): Builder
    {
        foreach ($filters as $key => $value) {
            if ($value !== null && $value !== '') {
                $this->applyFilter($query, $key, $value);
            }
        }
        return $query;
    }

    protected function applyFilter($query, string $key, $value): void
    {
        // Implement filter logic based on key
    }
}
```

**Use in**:
- `RiwayatController`
- `SiswaController`
- `UserController`
- All controllers with filtering

---

### 3.2 HasStatistics Trait
**File**: `app/Traits/HasStatistics.php`

**Purpose**: Common statistics calculation

**Methods**:
```php
trait HasStatistics
{
    protected function calculateStats(Collection $data): array
    {
        return [
            'total' => $data->count(),
            'average' => $data->avg('poin'),
            'max' => $data->max('poin'),
            'min' => $data->min('poin'),
        ];
    }
}
```

---

### 3.3 LogsActivity Trait
**File**: `app/Traits/LogsActivity.php`

**Purpose**: Automatic activity logging

**Methods**:
```php
trait LogsActivity
{
    protected function logActivity(string $action, string $description): void
    {
        app(AuditService::class)->logActivity(
            $action,
            $description,
            $this
        );
    }
}
```

**Use in Models** that need audit trail

---

## PHASE 4: REFACTOR CONTROLLERS

### 4.1 Standard Controller Pattern

#### Before (Bad):
```php
class ExampleController extends Controller
{
    public function index()
    {
        // Complex query
        $data = DB::table('table')
            ->join('other_table', ...)
            ->where(...)
            ->get();

        // Complex calculation
        $stats = [
            'total' => $data->count(),
            'average' => $data->avg('field'),
            // ... more calculations
        ];

        return view('example.index', compact('data', 'stats'));
    }
}
```

#### After (Good):
```php
class ExampleController extends Controller
{
    protected ExampleService $service;

    public function __construct(ExampleService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['filter1', 'filter2']);
        $data = $this->service->getData($filters);
        $stats = $this->service->getStats($filters);

        return view('example.index', compact('data', 'stats'));
    }
}
```

---

### 4.2 Controllers to Refactor (Priority Order)

#### High Priority:
1. **Dashboard Controllers** - Extract stats to StatisticsService
2. **ReportController** - Extract to ReportService
3. **DataJurusanController** - Use StatisticsService
4. **DataKelasController** - Use StatisticsService

#### Medium Priority:
5. **RiwayatController** - Use HasFilters trait
6. **SiswaController** - Use HasFilters trait
7. **UserController** - Use HasFilters trait

#### Low Priority:
8. All other controllers - Apply patterns consistently

---

## PHASE 5: UPDATE ROUTES

### 5.1 Route Organization

#### Current (Messy):
```php
Route::get('/jurusan', [JurusanController::class, 'index']);
Route::get('/kelas', [KelasController::class, 'index']);
Route::get('/siswa', [SiswaController::class, 'index']);
// ... scattered everywhere
```

#### Proposed (Organized):
```php
// ============================================
// MASTER DATA ROUTES
// ============================================
Route::prefix('master-data')->name('master-data.')->group(function () {
    Route::resource('jurusan', MasterData\JurusanController::class);
    Route::resource('kelas', MasterData\KelasController::class);
    Route::resource('siswa', MasterData\SiswaController::class);
    Route::resource('jenis-pelanggaran', MasterData\JenisPelanggaranController::class);
});

// ============================================
// PELANGGARAN ROUTES
// ============================================
Route::prefix('pelanggaran')->name('pelanggaran.')->group(function () {
    Route::get('/catat', [Pelanggaran\PelanggaranController::class, 'create'])->name('create');
    Route::post('/catat', [Pelanggaran\PelanggaranController::class, 'store'])->name('store');
    
    Route::get('/riwayat', [Pelanggaran\RiwayatController::class, 'index'])->name('riwayat.index');
    Route::get('/riwayat/saya', [Pelanggaran\MyRiwayatController::class, 'index'])->name('my-riwayat.index');
    
    Route::resource('tindak-lanjut', Pelanggaran\TindakLanjutController::class);
});

// ============================================
// RULES ROUTES
// ============================================
Route::prefix('rules')->name('rules.')->group(function () {
    Route::resource('frequency', Rules\FrequencyRulesController::class);
    Route::resource('pembinaan-internal', Rules\PembinaanInternalRulesController::class);
    Route::resource('settings', Rules\RulesEngineSettingsController::class);
});

// ... and so on
```

---

## PHASE 6: TESTING & VALIDATION

### 6.1 Automated Tests (If Exists)
```bash
php artisan test
```

### 6.2 Manual Testing Checklist

#### For Each Moved Controller:
- [ ] Route accessible
- [ ] View renders correctly
- [ ] CRUD operations work
- [ ] Filters work
- [ ] Validation works
- [ ] Authorization works
- [ ] No console errors

#### For Each Refactored Service:
- [ ] Methods return expected data
- [ ] Error handling works
- [ ] Caching works (if applicable)
- [ ] Performance acceptable

---

## PHASE 7: DOCUMENTATION

### 7.1 Update Architecture Documentation
**File**: `docs/ARCHITECTURE.md`

**Content**:
- New folder structure
- Service layer explanation
- Naming conventions
- Design patterns used

### 7.2 Update Developer Guide
**File**: `docs/DEVELOPER_GUIDE.md`

**Content**:
- How to create new controllers
- How to create new services
- How to use traits
- Code examples

### 7.3 Update CHANGELOG
**File**: `.kiro/specs/backend-refactoring/CHANGELOG.md`

**Content**:
- List all moved files
- List all new services
- Breaking changes (if any)
- Migration notes

---

## IMPLEMENTATION CHECKLIST

### Phase 1: Folder Restructuring
- [ ] Create new folder structure
- [ ] Move MasterData controllers
- [ ] Move Pelanggaran controllers
- [ ] Move Rules controllers
- [ ] Move KepalaSekolah controllers
- [ ] Move Data controllers
- [ ] Move Audit controllers
- [ ] Move User controllers
- [ ] Move Utility controllers
- [ ] Update all namespaces
- [ ] Update all routes
- [ ] Test all routes

### Phase 2: Service Layer
- [ ] Move existing services to subfolders
- [ ] Create ReportService
- [ ] Create StatisticsService
- [ ] Create AuditService
- [ ] Create service interfaces (optional)
- [ ] Update service bindings
- [ ] Test all services

### Phase 3: Traits
- [ ] Create HasFilters trait
- [ ] Create HasStatistics trait
- [ ] Create LogsActivity trait
- [ ] Apply traits to controllers
- [ ] Test trait functionality

### Phase 4: Refactor Controllers
- [ ] Refactor Dashboard controllers
- [ ] Refactor ReportController
- [ ] Refactor Data controllers
- [ ] Refactor other controllers
- [ ] Standardize dependency injection
- [ ] Test all controllers

### Phase 5: Routes
- [ ] Organize routes by domain
- [ ] Add route prefixes
- [ ] Add route names
- [ ] Test all routes

### Phase 6: Testing
- [ ] Run automated tests
- [ ] Manual testing
- [ ] Performance testing
- [ ] Security testing

### Phase 7: Documentation
- [ ] Update architecture docs
- [ ] Update developer guide
- [ ] Update CHANGELOG
- [ ] Update README

---

## ROLLBACK PLAN

### If Something Goes Wrong:

1. **Git Reset**:
```bash
git reset --hard HEAD~1
```

2. **Restore from Backup**:
```bash
git checkout backup-branch
```

3. **Selective Revert**:
```bash
git revert <commit-hash>
```

---

## SUCCESS CRITERIA

### Code Quality:
- ✅ No duplicated code
- ✅ Clear separation of concerns
- ✅ Consistent naming conventions
- ✅ Proper dependency injection
- ✅ All controllers < 200 lines
- ✅ All services testable

### Structure:
- ✅ All controllers in appropriate folders
- ✅ All services in appropriate folders
- ✅ No files in wrong locations
- ✅ Clear folder hierarchy

### Functionality:
- ✅ All features work as before
- ✅ No broken routes
- ✅ No performance degradation
- ✅ All tests pass

---

**Ready to implement?** Let me know and I'll start with Phase 1!
