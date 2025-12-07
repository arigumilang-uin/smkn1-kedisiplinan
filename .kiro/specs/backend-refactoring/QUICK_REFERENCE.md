# Backend Refactoring - Quick Reference Guide

**Last Updated**: 2025-12-07  
**Status**: Phase 4 Complete (57%)

---

## Current Structure

### Controllers Organization

```
app/Http/Controllers/
├── Auth/                    # Authentication controllers
├── Dashboard/               # Dashboard controllers (by role)
│   ├── AdminDashboardController.php (uses HasStatistics)
│   ├── KepsekDashboardController.php (uses HasStatistics)
│   ├── KaprodiDashboardController.php
│   ├── WaliKelasDashboardController.php
│   ├── WaliMuridDashboardController.php
│   ├── WakaSaranaDashboardController.php
│   └── DeveloperDashboardController.php
├── MasterData/              # Master data CRUD
│   ├── JurusanController.php
│   ├── KelasController.php
│   ├── SiswaController.php (uses HasFilters)
│   └── JenisPelanggaranController.php
├── Pelanggaran/             # Violation management
│   ├── PelanggaranController.php
│   ├── RiwayatController.php (uses HasFilters)
│   ├── MyRiwayatController.php
│   └── TindakLanjutController.php
├── Rules/                   # Rules engine
│   ├── FrequencyRulesController.php
│   ├── PembinaanInternalRulesController.php
│   └── RulesEngineSettingsController.php
├── Data/                    # Data views (read-only)
│   ├── DataJurusanController.php (uses HasStatistics)
│   └── DataKelasController.php (uses HasStatistics)
├── User/                    # User management
│   ├── UserController.php (uses HasFilters)
│   └── ProfileController.php
├── Utility/                 # Utility controllers
│   ├── FileController.php
│   └── DeveloperController.php
├── Audit/                   # Audit trail
│   └── ActivityLogController.php
└── Report/                  # Reports (function-based naming)
    ├── ApprovalController.php
    ├── ReportController.php
    └── SiswaPerluPembinaanController.php
```

### Services Organization

```
app/Services/
├── Pelanggaran/
│   ├── PelanggaranRulesEngine.php
│   └── SuratPanggilanService.php
├── Rules/
│   └── RulesEngineSettingsService.php
└── User/
    ├── RoleService.php
    └── UserNamingService.php
```

### Traits Organization

```
app/Traits/
├── HasFilters.php          # Common filtering functionality
├── HasStatistics.php       # Statistics calculation methods
└── LogsActivity.php        # Enhanced activity logging
```

---

## Traits Usage

### 1. HasFilters Trait

**Used by**: RiwayatController, SiswaController, UserController

**Methods**:
```php
// Apply multiple filters
$filters = $this->getFilters(['kelas_id', 'jurusan_id', 'search']);
$this->applyFilters($query, $filters);

// Apply search
$this->applySearch($query, $searchTerm, ['nama', 'nis', 'email']);
```

**Supported Filters**:
- `tanggal_dari` / `tanggal_sampai` - Date range
- `search` / `nama` - Search with LIKE
- `role_id`, `kelas_id`, `jurusan_id` - Exact match
- `status`, `is_active` - Status filters

---

### 2. HasStatistics Trait

**Used by**: AdminDashboardController, KepsekDashboardController, DataJurusanController, DataKelasController

**Methods**:
```php
// Basic stats
$stats = $this->calculateStats($data, 'poin');
// Returns: ['total', 'average', 'max', 'min', 'sum']

// Group by period
$byMonth = $this->groupByPeriod($data, 'tanggal_kejadian', 'month');

// Top items
$topSiswa = $this->getTopItems($data, 'total_pelanggaran', 10);

// Growth calculation
$growth = $this->calculateGrowth($current, $previous);
// Returns: ['value', 'percentage', 'direction']

// Format card
$card = $this->formatStatCard('Total Siswa', 150, 'fa-users', 'primary');
```

---

### 3. LogsActivity Trait

**Used by**: Siswa, RiwayatPelanggaran, TindakLanjut models

**Automatic Logging**:
- Logs on create/update/delete
- Only logs dirty attributes
- Custom descriptions in Indonesian

**Manual Logging**:
```php
// Log custom activity
$siswa->logCustomActivity('Siswa dipindahkan ke kelas baru', [
    'old_kelas' => 'X RPL 1',
    'new_kelas' => 'X RPL 2',
]);

// Log specific action
$siswa->logAction('promoted', 'Siswa naik kelas', ['from' => 'X', 'to' => 'XI']);

// Get activity log
$activities = $siswa->getActivityLog(10);

// Check if has logs
if ($siswa->hasActivityLog()) {
    // Do something
}
```

---

## Naming Conventions

### ✅ Correct (Function-Based)
```
Report/                     # Function: Reporting
Dashboard/                  # Function: Dashboard views
MasterData/                 # Function: Master data management
Pelanggaran/                # Function: Violation management
```

### ❌ Incorrect (Role-Based)
```
KepalaSekolah/             # Role-based (WRONG)
WaliKelas/                 # Role-based (WRONG)
Operator/                  # Role-based (WRONG)
```

---

## File Naming Patterns

### Controllers
```
{Entity}Controller.php          # CRUD controller
{Role}DashboardController.php   # Dashboard controller
{Function}Controller.php        # Functional controller
```

### Services
```
{Entity}Service.php            # Entity service
{Function}Service.php          # Functional service
```

### Models
```
{Entity}.php                   # Singular, PascalCase
```

---

## Common Patterns

### Controller with Filtering
```php
use App\Traits\HasFilters;

class SiswaController extends Controller
{
    use HasFilters;

    public function index(Request $request)
    {
        $query = Siswa::query();
        
        // Get and apply filters
        $filters = $this->getFilters(['kelas_id', 'jurusan_id', 'search']);
        $this->applyFilters($query, $filters);
        
        return view('siswa.index', [
            'siswa' => $query->paginate(20)
        ]);
    }
}
```

### Dashboard with Statistics
```php
use App\Traits\HasStatistics;

class AdminDashboardController extends Controller
{
    use HasStatistics;

    public function index()
    {
        $pelanggaran = RiwayatPelanggaran::all();
        
        // Calculate stats
        $stats = $this->calculateStats($pelanggaran, 'poin');
        $byMonth = $this->groupByPeriod($pelanggaran, 'tanggal_kejadian', 'month');
        $topViolators = $this->getTopItems($pelanggaran, 'poin', 10);
        
        return view('dashboard.admin', compact('stats', 'byMonth', 'topViolators'));
    }
}
```

### Model with Activity Logging
```php
use App\Traits\LogsActivity;

class Siswa extends Model
{
    use LogsActivity;

    protected function getLogAttributes(): array
    {
        return ['nama_siswa', 'nisn', 'kelas_id'];
    }

    protected function getActivityDescription(string $eventName): string
    {
        $userName = auth()->user()?->nama ?? 'System';
        
        return match($eventName) {
            'created' => "{$userName} menambahkan siswa {$this->nama_siswa}",
            'updated' => "{$userName} mengubah data siswa {$this->nama_siswa}",
            'deleted' => "{$userName} menghapus siswa {$this->nama_siswa}",
            default => parent::getActivityDescription($eventName),
        };
    }
}
```

---

## Progress Checklist

### ✅ Completed Phases
- [x] Phase 1: Folder Restructuring (100%)
- [x] Phase 2: Update Routes (100%)
- [x] Phase 3: Service Organization (100%)
- [x] Phase 4: Traits Implementation (100%)

### ⏳ Pending Phases
- [ ] Phase 5: Refactor Controllers (0%)
- [ ] Phase 6: Testing (0%)
- [ ] Phase 7: Documentation (0%)

**Total Progress**: 57% (4/7 phases)

---

## Quick Commands

### Clear Caches
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Cache Routes
```bash
php artisan route:cache
php artisan config:cache
```

### Check Routes
```bash
php artisan route:list
php artisan route:list --name=siswa
```

### Run Diagnostics
Check for errors in specific files using IDE diagnostics tool.

---

## Documentation Files

### Main Documentation
- `ANALYSIS.md` - Complete backend analysis
- `IMPLEMENTATION_PLAN.md` - Detailed implementation plan
- `PROGRESS.md` - Progress tracking

### Phase Summaries
- `PHASE_1_2_COMPLETE.md` - Folder restructuring & routes
- `PHASE_3_COMPLETE.md` - Service organization
- `PHASE_4_COMPLETE.md` - Traits implementation
- `CONSISTENCY_FIX.md` - Naming consistency fix

### Guides
- `TRAITS_GUIDE.md` - Complete traits usage guide
- `TRAITS_APPLIED_SUMMARY.md` - Traits application summary
- `QUICK_REFERENCE.md` - This file

---

## Need Help?

### For Filtering Issues
See: `TRAITS_GUIDE.md` → HasFilters section

### For Statistics Issues
See: `TRAITS_GUIDE.md` → HasStatistics section

### For Activity Logging Issues
See: `TRAITS_GUIDE.md` → LogsActivity section

### For Structure Questions
See: `ANALYSIS.md` and `IMPLEMENTATION_PLAN.md`

---

**Last Updated**: 2025-12-07  
**Maintained by**: Backend Refactoring Team  
**Status**: Active Development
