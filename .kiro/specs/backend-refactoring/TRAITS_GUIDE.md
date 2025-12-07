# Backend Traits - Usage Guide

## Overview
Traits yang dibuat untuk mengurangi code duplication dan meningkatkan reusability di seluruh aplikasi.

---

## 1. HasFilters Trait

**Location**: `app/Traits/HasFilters.php`

**Purpose**: Menyediakan common filtering functionality untuk controllers

### Features:
- ✅ Apply multiple filters to query
- ✅ Date range filtering
- ✅ Search across multiple columns
- ✅ Exact match filtering
- ✅ Status filtering

### Usage Example:

```php
<?php

namespace App\Http\Controllers;

use App\Traits\HasFilters;
use App\Models\Siswa;

class SiswaController extends Controller
{
    use HasFilters;

    public function index()
    {
        // Get filters from request
        $filters = $this->getFilters([
            'kelas_id',
            'jurusan_id',
            'search',
            'is_active'
        ]);

        // Apply filters to query
        $query = Siswa::query();
        $this->applyFilters($query, $filters);

        // Get results
        $siswa = $query->paginate(20);

        return view('siswa.index', compact('siswa'));
    }

    // Or use search method
    public function search()
    {
        $searchTerm = request('search');
        
        $query = Siswa::query();
        $this->applySearch($query, $searchTerm, ['nama', 'nis', 'email']);

        return $query->get();
    }
}
```

### Available Methods:

#### `applyFilters(Builder $query, array $filters): Builder`
Apply multiple filters to a query builder.

#### `applyFilter(Builder $query, string $key, $value): void`
Apply a single filter. Automatically handles:
- Date ranges (`tanggal_dari`, `tanggal_sampai`)
- Search (`search`, `nama`)
- Exact matches (IDs, status)

#### `getFilters(array $allowedFilters): array`
Get filter values from request, removing empty values.

#### `applySearch(Builder $query, string $searchTerm, array $columns): Builder`
Apply search across multiple columns.

### Customization:
Override `applyFilter()` method in your controller to add custom filter logic:

```php
protected function applyFilter(Builder $query, string $key, $value): void
{
    // Custom filter for your controller
    if ($key === 'custom_filter') {
        $query->where('custom_field', $value);
        return;
    }

    // Call parent for default filters
    parent::applyFilter($query, $key, $value);
}
```

---

## 2. HasStatistics Trait

**Location**: `app/Traits/HasStatistics.php`

**Purpose**: Menyediakan common statistics calculation methods

### Features:
- ✅ Calculate basic stats (avg, max, min, sum)
- ✅ Group data by period (month/week/day)
- ✅ Calculate percentages
- ✅ Get top N items
- ✅ Calculate growth rate
- ✅ Format stat cards for dashboard

### Usage Example:

```php
<?php

namespace App\Http\Controllers\Dashboard;

use App\Traits\HasStatistics;
use App\Models\RiwayatPelanggaran;

class AdminDashboardController extends Controller
{
    use HasStatistics;

    public function index()
    {
        // Get data
        $pelanggaran = RiwayatPelanggaran::all();

        // Calculate basic stats
        $stats = $this->calculateStats($pelanggaran, 'poin');
        // Returns: ['total' => 100, 'average' => 25.5, 'max' => 50, 'min' => 5, 'sum' => 2550]

        // Group by month
        $byMonth = $this->groupByPeriod($pelanggaran, 'tanggal_kejadian', 'month');

        // Get top 10 violators
        $topViolators = $this->getTopItems($pelanggaran, 'poin', 10);

        // Calculate growth
        $currentMonth = $pelanggaran->count();
        $previousMonth = 80;
        $growth = $this->calculateGrowth($currentMonth, $previousMonth);
        // Returns: ['value' => 20, 'percentage' => 25, 'direction' => 'up']

        // Format for dashboard card
        $card = $this->formatStatCard(
            'Total Pelanggaran',
            $stats['total'],
            'fa-exclamation-triangle',
            'danger',
            ['growth' => $growth]
        );

        return view('dashboard.admin', compact('stats', 'byMonth', 'topViolators', 'card'));
    }
}
```

### Available Methods:

#### `calculateStats(Collection $data, string $field = 'poin'): array`
Calculate basic statistics (total, average, max, min, sum).

#### `groupByPeriod(Collection $data, string $dateField, string $period): array`
Group data by period ('month', 'week', 'day').

#### `calculatePercentage($part, $total, int $decimals = 2): float`
Calculate percentage with specified decimal places.

#### `getTopItems(Collection $data, string $field, int $limit = 10, string $order = 'desc'): Collection`
Get top N items sorted by field.

#### `calculateGrowth($current, $previous): array`
Calculate growth rate between two values.

#### `formatStatCard(string $title, $value, string $icon, string $color, array $additional): array`
Format statistics for dashboard cards.

---

## 3. LogsActivity Trait

**Location**: `app/Traits/LogsActivity.php`

**Purpose**: Enhanced activity logging with custom methods

### Features:
- ✅ Automatic logging on create/update/delete
- ✅ Log only dirty attributes
- ✅ Custom activity descriptions
- ✅ Log custom activities
- ✅ Get activity history

### Usage Example:

```php
<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use LogsActivity;

    protected $fillable = ['nama', 'nis', 'kelas_id', 'jurusan_id'];

    // Optional: Specify which attributes to log
    protected function getLogAttributes(): array
    {
        return ['nama', 'nis', 'kelas_id', 'jurusan_id'];
    }

    // Optional: Custom activity description
    protected function getActivityDescription(string $eventName): string
    {
        $userName = auth()->user()?->nama ?? 'System';
        
        return match($eventName) {
            'created' => "{$userName} menambahkan siswa {$this->nama}",
            'updated' => "{$userName} mengubah data siswa {$this->nama}",
            'deleted' => "{$userName} menghapus siswa {$this->nama}",
            default => parent::getActivityDescription($eventName),
        };
    }
}
```

### Manual Logging:

```php
// In controller
$siswa = Siswa::find(1);

// Log custom activity
$siswa->logCustomActivity('Siswa dipindahkan ke kelas baru', [
    'old_kelas' => 'X RPL 1',
    'new_kelas' => 'X RPL 2',
]);

// Log specific action
$siswa->logAction('promoted', 'Siswa naik kelas', [
    'from' => 'X',
    'to' => 'XI',
]);

// Get activity log
$activities = $siswa->getActivityLog(10);

// Check if has logs
if ($siswa->hasActivityLog()) {
    // Do something
}
```

### Available Methods:

#### `getActivitylogOptions(): LogOptions`
Configure logging options (automatically called).

#### `getLogAttributes(): array`
Specify which attributes to log (override in model).

#### `getActivityDescription(string $eventName): string`
Get description for activity (override for custom descriptions).

#### `logCustomActivity(string $description, array $properties = []): void`
Log custom activity with properties.

#### `logAction(string $event, string $description, array $properties = []): void`
Log action with specific event name.

#### `getActivityLog(int $limit = 10): Collection`
Get activity log for this model.

#### `hasActivityLog(): bool`
Check if model has any activity logs.

---

## Benefits of Using Traits

### 1. Code Reusability
- ✅ Write once, use everywhere
- ✅ No code duplication
- ✅ Consistent behavior across controllers

### 2. Maintainability
- ✅ Fix bugs in one place
- ✅ Easy to update functionality
- ✅ Clear separation of concerns

### 3. Testability
- ✅ Test traits independently
- ✅ Mock trait methods easily
- ✅ Better unit testing

### 4. Readability
- ✅ Controllers stay clean
- ✅ Business logic separated
- ✅ Easy to understand

---

## Controllers That Should Use These Traits

### HasFilters:
- ✅ `RiwayatController` (filter pelanggaran)
- ✅ `SiswaController` (filter siswa)
- ✅ `UserController` (filter users)
- ✅ `ReportController` (filter reports)

### HasStatistics:
- ✅ All Dashboard controllers
- ✅ `DataJurusanController`
- ✅ `DataKelasController`
- ✅ `ReportController`

### LogsActivity:
- ✅ `Siswa` model
- ✅ `User` model (already has it)
- ✅ `RiwayatPelanggaran` model
- ✅ `TindakLanjut` model
- ✅ All models that need audit trail

---

## Next Steps

### 1. Apply Traits to Existing Controllers
Refactor existing controllers to use these traits.

### 2. Test Functionality
Ensure all features work correctly after applying traits.

### 3. Document Usage
Add PHPDoc comments to controllers using traits.

### 4. Create More Traits (Optional)
- `HasExport` - Common export functionality
- `HasPagination` - Custom pagination
- `HasValidation` - Common validation rules

---

**Created**: 2025-12-07
**Status**: Ready to use
**Version**: 1.0
