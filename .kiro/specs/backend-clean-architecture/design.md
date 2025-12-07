# Design Document: Backend Clean Architecture Refactoring

## Overview

This design document outlines the comprehensive refactoring of the SMKN1 Disciplinary System backend to implement clean architecture principles and Laravel best practices. The refactoring will transform the current monolithic controller-heavy architecture into a well-structured, maintainable, and performant system with clear separation of concerns.

### Current State Analysis

The existing codebase exhibits several architectural issues:
- **Fat Controllers**: Controllers contain business logic, query building, and data manipulation (200+ lines)
- **Missing Abstraction Layers**: No repository pattern, direct Eloquent queries in controllers
- **Inconsistent Data Handling**: Raw arrays passed between layers instead of type-safe DTOs
- **Performance Issues**: N+1 queries, missing indexes, no caching strategy
- **Validation Scattered**: Inline validation in controllers instead of Form Requests
- **Monolithic Routes**: Single web.php file with 200+ routes
- **Limited Testing**: No clear boundaries for unit testing

### Target Architecture

The refactored system will implement a layered architecture:

```
┌─────────────────────────────────────────────────────────┐
│                    HTTP Layer                            │
│  Controllers (thin) + Form Requests + Routes             │
└────────────────┬────────────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────────────┐
│                  Service Layer                           │
│  Business Logic + Orchestration + Validation             │
└────────────────┬────────────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────────────┐
│                Repository Layer                          │
│  Data Access + Query Building + Filtering                │
└────────────────┬────────────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────────────┐
│                  Model Layer                             │
│  Eloquent Models + Relationships + Casts                 │
└─────────────────────────────────────────────────────────┘
```

### Key Improvements

1. **Separation of Concerns**: Each layer has a single, well-defined responsibility
2. **Type Safety**: DTOs ensure predictable data structures across layers
3. **Testability**: Clear boundaries enable comprehensive unit and integration testing
4. **Performance**: Optimized queries, eager loading, caching, and proper indexing
5. **Maintainability**: Consistent patterns, clear naming, and organized folder structure
6. **Scalability**: Repository pattern allows easy database switching and query optimization

## Architecture

### Layered Architecture Pattern

The system follows a strict layered architecture where each layer can only communicate with the layer directly below it:

#### 1. HTTP Layer (Controllers + Form Requests + Routes)

**Responsibilities:**
- Receive HTTP requests
- Validate input using Form Requests
- Call appropriate service methods
- Format and return HTTP responses
- Handle HTTP-specific concerns (redirects, sessions, cookies)

**Rules:**
- Controllers MUST NOT contain business logic
- Controllers MUST NOT build database queries
- Controllers MUST delegate to services for all business operations
- Controllers SHOULD be under 20 lines per method

#### 2. Service Layer

**Responsibilities:**
- Implement business rules and logic
- Orchestrate operations across multiple repositories
- Perform complex validations
- Handle transactions
- Trigger events and notifications
- Transform data between DTOs

**Rules:**
- Services MUST NOT access request/response objects directly
- Services MUST NOT inject or use Illuminate\Http\Request or Form Request classes
- Services MUST ONLY accept DTOs or primitive types as method parameters
- Services MUST use repositories for data access
- Services MUST use DTOs for data transfer
- Services SHOULD be focused on single domain concepts

#### 3. Repository Layer

**Responsibilities:**
- Encapsulate data access logic
- Build and execute database queries
- Handle filtering, sorting, and pagination
- Manage query optimization (eager loading, select specific columns)
- Cache query results where appropriate

**Rules:**
- Repositories MUST NOT contain business logic
- Repositories MUST implement interface contracts
- Repositories SHOULD use query scopes from models
- Repositories MUST return DTOs or Collections

#### 4. Model Layer

**Responsibilities:**
- Define database table structure
- Define relationships between entities
- Cast attributes to appropriate types
- Provide simple query scopes
- Define fillable/guarded attributes

**Rules:**
- Models MUST NOT contain business logic
- Models MUST NOT perform complex queries
- Models SHOULD only have lightweight scopes
- Models MUST define all relationships with return types

### Dependency Injection Flow

```php
// Service Provider binds interfaces to implementations
$this->app->bind(SiswaRepositoryInterface::class, SiswaRepository::class);
$this->app->bind(PelanggaranServiceInterface::class, PelanggaranService::class);

// Controller receives dependencies via constructor
class SiswaController extends Controller
{
    public function __construct(
        private SiswaService $siswaService
    ) {}
}

// Service receives repository dependencies
class SiswaService
{
    public function __construct(
        private SiswaRepositoryInterface $siswaRepository,
        private KelasRepositoryInterface $kelasRepository
    ) {}
}

// Repository receives model
class SiswaRepository implements SiswaRepositoryInterface
{
    public function __construct(
        private Siswa $model
    ) {}
}
```

### Data Flow: HTTP Request to Service (Pure Clean Architecture)

The system enforces strict decoupling between HTTP layer and business logic:

```
┌─────────────────────────────────────────────────────────────────┐
│ 1. HTTP Request arrives                                          │
│    POST /siswa with form data                                    │
└────────────────────┬────────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────────┐
│ 2. Form Request validates                                        │
│    CreateSiswaRequest->rules()                                   │
│    CreateSiswaRequest->authorize()                               │
└────────────────────┬────────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────────┐
│ 3. Controller converts to DTO                                    │
│    $dto = SiswaData::from($request->validated())                 │
│    ✅ HTTP layer ends here - no Request object beyond this point │
└────────────────────┬────────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────────┐
│ 4. Service receives pure DTO                                     │
│    $siswaService->createSiswa($dto)                              │
│    ✅ Service only knows about DTOs, not HTTP                    │
└────────────────────┬────────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────────┐
│ 5. Repository receives DTO                                       │
│    $siswaRepository->create($dto)                                │
│    ✅ Repository only knows about DTOs and primitives            │
└────────────────────┬────────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────────┐
│ 6. Model persists to database                                    │
│    Siswa::create($dto->toArray())                                │
└─────────────────────────────────────────────────────────────────┘
```

**Key Principle**: 
- ❌ **NEVER** pass `Illuminate\Http\Request` or `FormRequest` to Service or Repository
- ✅ **ALWAYS** convert to DTO in Controller first
- ✅ Service and Repository remain **HTTP-agnostic** and **testable**

## Components and Interfaces

### Critical Architecture Rules

Before diving into component details, these rules MUST be followed throughout the implementation:

1. **HTTP Decoupling**: Services and Repositories MUST NEVER receive `Illuminate\Http\Request` or `FormRequest` objects
2. **DTO Conversion**: Controllers MUST convert Form Requests to DTOs before passing to Services
3. **Type Safety**: Use PHP Native Enums for all status/type columns instead of magic strings
4. **Pure Functions**: Services should be HTTP-agnostic and testable in isolation

### 1. Data Transfer Objects (DTOs)

DTOs provide type-safe data structures for transferring data between layers. We'll use Spatie's Laravel Data package for DTO implementation with built-in validation.

**Important**: DTOs are the ONLY way to pass data from Controllers to Services and from Services to Repositories. This ensures clean separation and testability.

#### Core DTO Structure

```php
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Max;

class SiswaData extends Data
{
    public function __construct(
        public ?int $id,
        
        #[Required, Max(255)]
        public string $nama_siswa,
        
        #[Required]
        public string $nisn,
        
        #[Required]
        public int $kelas_id,
        
        public ?int $wali_murid_user_id,
        public ?string $nomor_hp_wali_murid,
    ) {}
    
    public static function rules(): array
    {
        return [
            'nisn' => ['required', 'numeric', 'unique:siswa,nisn'],
            'nama_siswa' => ['required', 'string', 'max:255'],
            'kelas_id' => ['required', 'exists:kelas,id'],
        ];
    }
}
```

#### Filter DTO Structure

Filter DTOs are used to pass search/filter criteria from controllers to repositories, maintaining clean architecture principles:

```php
use Spatie\LaravelData\Data;

class SiswaFilterData extends Data
{
    public function __construct(
        public ?string $search = null,
        public ?int $kelas_id = null,
        public ?int $jurusan_id = null,
        public ?StatusSiswa $status = null,
        public int $perPage = 20,
        public string $sortBy = 'nama_siswa',
        public string $sortDirection = 'asc',
    ) {}
}

class RiwayatPelanggaranFilterData extends Data
{
    public function __construct(
        public ?int $siswa_id = null,
        public ?int $jenis_pelanggaran_id = null,
        public ?int $guru_pencatat_id = null,
        public ?string $tanggal_dari = null,
        public ?string $tanggal_sampai = null,
        public ?TingkatPelanggaran $tingkat = null,
        public int $perPage = 20,
    ) {}
}
```

#### DTO Categories

**Entity DTOs**: Represent domain entities
- `SiswaData`
- `RiwayatPelanggaranData`
- `TindakLanjutData`
- `UserData`
- `JenisPelanggaranData`

**Filter DTOs**: Represent filter/search criteria (used by repositories)
- `SiswaFilterData`
- `RiwayatPelanggaranFilterData`
- `TindakLanjutFilterData`

**Response DTOs**: Represent outgoing responses
- `SiswaDetailResponse`
- `PelanggaranSummaryResponse`
- `DashboardStatsResponse`

### 2. Repository Interfaces and Implementations

Repositories encapsulate all data access logic and provide a clean API for services.

#### Base Repository Interface

```php
interface BaseRepositoryInterface
{
    public function find(int $id): ?Data;
    public function findOrFail(int $id): Data;
    public function all(): Collection;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function create(Data $data): Data;
    public function update(int $id, Data $data): Data;
    public function delete(int $id): bool;
}
```

#### Domain-Specific Repository Interfaces

```php
interface SiswaRepositoryInterface extends BaseRepositoryInterface
{
    public function findByNisn(string $nisn): ?SiswaData;
    public function findByKelas(int $kelasId): Collection;
    public function findByJurusan(int $jurusanId): Collection;
    public function findByWaliMurid(int $waliMuridId): Collection;
    public function searchByName(string $keyword): Collection;
    public function withViolations(): Collection;
    public function withActiveCases(): Collection;
    public function filterAndPaginate(SiswaFilterData $filters): LengthAwarePaginator;
}

interface RiwayatPelanggaranRepositoryInterface extends BaseRepositoryInterface
{
    public function findBySiswa(int $siswaId): Collection;
    public function findByDateRange(Carbon $startDate, Carbon $endDate): Collection;
    public function findByJenisPelanggaran(int $jenisId): Collection;
    public function findByGuruPencatat(int $guruId): Collection;
    public function countBySiswaAndJenis(int $siswaId, int $jenisId): int;
    public function getTotalPoinBySiswa(int $siswaId): int;
    public function filterAndPaginate(RiwayatPelanggaranFilterData $filters): LengthAwarePaginator;
}
```

#### Repository Implementation Pattern

```php
class SiswaRepository implements SiswaRepositoryInterface
{
    public function __construct(
        private Siswa $model
    ) {}
    
    public function find(int $id): ?SiswaData
    {
        $siswa = $this->model->find($id);
        return $siswa ? SiswaData::from($siswa) : null;
    }
    
    public function filterAndPaginate(SiswaFilterData $filters): LengthAwarePaginator
    {
        $query = $this->model->query()
            ->with(['kelas.jurusan', 'waliMurid'])
            ->select(['id', 'nama_siswa', 'nisn', 'kelas_id', 'wali_murid_user_id']);
        
        // Apply filters
        if ($filters->search) {
            $query->where(function($q) use ($filters) {
                $q->where('nama_siswa', 'like', "%{$filters->search}%")
                  ->orWhere('nisn', 'like', "%{$filters->search}%");
            });
        }
        
        if ($filters->kelas_id) {
            $query->where('kelas_id', $filters->kelas_id);
        }
        
        if ($filters->jurusan_id) {
            $query->whereHas('kelas', fn($q) => $q->where('jurusan_id', $filters->jurusan_id));
        }
        
        return $query->orderBy('nama_siswa')->paginate($filters->perPage ?? 20);
    }
}
```

### 3. Service Layer

Services contain business logic and orchestrate operations across multiple repositories.

#### Service Structure

```php
class SiswaService
{
    public function __construct(
        private SiswaRepositoryInterface $siswaRepository,
        private KelasRepositoryInterface $kelasRepository,
        private UserRepositoryInterface $userRepository,
        private UserNamingService $userNamingService
    ) {}
    
    public function createSiswa(SiswaData $data, bool $createWali = false): SiswaData
    {
        DB::beginTransaction();
        try {
            // Business logic: Create wali murid if requested
            $waliMuridId = null;
            if ($createWali && !$data->wali_murid_user_id) {
                $waliMuridId = $this->createWaliMuridAccount($data->nisn, $data->nama_siswa);
            }
            
            // Create siswa with wali murid if created
            $siswaData = new SiswaData(
                id: null,
                nama_siswa: $data->nama_siswa,
                nisn: $data->nisn,
                kelas_id: $data->kelas_id,
                wali_murid_user_id: $waliMuridId ?? $data->wali_murid_user_id,
                nomor_hp_wali_murid: $data->nomor_hp_wali_murid
            );
            
            $siswa = $this->siswaRepository->create($siswaData);
            
            DB::commit();
            return $siswa;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    private function createWaliMuridAccount(string $nisn, string $namaSiswa): int
    {
        $username = $this->userNamingService->generateWaliMuridUsername($nisn);
        $password = $this->userNamingService->generateWaliMuridPassword($nisn);
        
        $userData = new UserData(
            id: null,
            nama: "Wali dari {$namaSiswa}",
            username: $username,
            email: "{$username}@no-reply.local",
            password: $password,
            role_id: Role::findByName('Wali Murid')->id
        );
        
        $user = $this->userRepository->create($userData);
        
        // Flash credentials for display
        session()->flash('wali_created', [
            'username' => $username,
            'password' => $password
        ]);
        
        return $user->id;
    }
    
    public function updateSiswa(int $id, SiswaData $data, int $currentUserId, string $userRole): SiswaData
    {
        // Business rule: Wali Kelas can only update phone number
        if ($userRole === 'Wali Kelas') {
            $siswa = $this->siswaRepository->findOrFail($id);
            
            // Verify authorization - Wali Kelas can only update their own class
            $currentUser = $this->userRepository->find($currentUserId);
            if ($siswa->kelas_id !== $currentUser->kelas_diampu_id) {
                throw new UnauthorizedException('Anda hanya dapat memperbarui siswa di kelas yang Anda ampu.');
            }
            
            // Limited update - only phone number
            $siswaData = new SiswaData(
                id: $id,
                nama_siswa: $siswa->nama_siswa,
                nisn: $siswa->nisn,
                kelas_id: $siswa->kelas_id,
                wali_murid_user_id: $siswa->wali_murid_user_id,
                nomor_hp_wali_murid: $data->nomor_hp_wali_murid
            );
        } else {
            // Full update for operators
            $siswaData = new SiswaData(
                id: $id,
                nama_siswa: $data->nama_siswa,
                nisn: $data->nisn,
                kelas_id: $data->kelas_id,
                wali_murid_user_id: $data->wali_murid_user_id,
                nomor_hp_wali_murid: $data->nomor_hp_wali_murid
            );
        }
        
        return $this->siswaRepository->update($id, $siswaData);
    }
    
    public function getFilteredSiswa(SiswaFilterData $filters): LengthAwarePaginator
    {
        return $this->siswaRepository->filterAndPaginate($filters);
    }
}
```

### 4. Form Request Validation

Form Requests handle input validation and authorization logic.

```php
class CreateSiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('Operator Sekolah');
    }
    
    public function rules(): array
    {
        return [
            'nisn' => ['required', 'numeric', 'unique:siswa,nisn'],
            'nama_siswa' => ['required', 'string', 'max:255'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'nomor_hp_wali_murid' => ['nullable', 'numeric'],
            'wali_murid_user_id' => ['nullable', 'exists:users,id'],
            'create_wali' => ['nullable', 'boolean'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'nisn.required' => 'NISN wajib diisi',
            'nisn.unique' => 'NISN sudah terdaftar',
            'nama_siswa.required' => 'Nama siswa wajib diisi',
            'kelas_id.required' => 'Kelas wajib dipilih',
            'kelas_id.exists' => 'Kelas tidak valid',
        ];
    }
}

class UpdateSiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['Operator Sekolah', 'Wali Kelas']);
    }
    
    public function rules(): array
    {
        $siswaId = $this->route('siswa');
        
        // Different rules based on user role
        if ($this->user()->hasRole('Wali Kelas')) {
            return [
                'nomor_hp_wali_murid' => ['nullable', 'numeric'],
            ];
        }
        
        return [
            'nisn' => ['required', 'numeric', "unique:siswa,nisn,{$siswaId}"],
            'nama_siswa' => ['required', 'string', 'max:255'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'nomor_hp_wali_murid' => ['nullable', 'numeric'],
            'wali_murid_user_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
```

### 5. Controller Structure

Controllers become thin orchestrators that delegate to services.

```php
class SiswaController extends Controller
{
    public function __construct(
        private SiswaService $siswaService
    ) {}
    
    public function index(FilterSiswaRequest $request)
    {
        // Convert Form Request to Filter DTO
        $filterData = SiswaFilterData::from($request->validated());
        
        $siswa = $this->siswaService->getFilteredSiswa($filterData);
        $allJurusan = $this->siswaService->getJurusanForFilter();
        $allKelas = $this->siswaService->getKelasForFilter();
        
        return view('siswa.index', compact('siswa', 'allJurusan', 'allKelas'));
    }
    
    public function create()
    {
        $kelas = $this->siswaService->getAllKelas();
        $waliMurid = $this->siswaService->getAvailableWaliMurid();
        
        return view('siswa.create', compact('kelas', 'waliMurid'));
    }
    
    public function store(CreateSiswaRequest $request)
    {
        // Convert Form Request to DTO
        $siswaData = SiswaData::from($request->validated());
        
        $siswa = $this->siswaService->createSiswa($siswaData, $request->boolean('create_wali'));
        
        return redirect()
            ->route('siswa.index')
            ->with('success', 'Data Siswa Berhasil Ditambahkan');
    }
    
    public function show(int $id)
    {
        $siswa = $this->siswaService->getSiswaDetail($id);
        
        return view('siswa.show', compact('siswa'));
    }
    
    public function edit(int $id)
    {
        $siswa = $this->siswaService->findSiswa($id);
        $kelas = $this->siswaService->getAllKelas();
        $waliMurid = $this->siswaService->getAvailableWaliMurid();
        
        return view('siswa.edit', compact('siswa', 'kelas', 'waliMurid'));
    }
    
    public function update(UpdateSiswaRequest $request, int $id)
    {
        // Convert Form Request to DTO
        $siswaData = SiswaData::from($request->validated());
        
        $siswa = $this->siswaService->updateSiswa(
            $id, 
            $siswaData, 
            auth()->id(), 
            auth()->user()->role->name
        );
        
        return redirect()
            ->route('siswa.index')
            ->with('success', 'Data siswa berhasil diperbarui.');
    }
    
    public function destroy(int $id)
    {
        $this->siswaService->deleteSiswa($id);
        
        return redirect()
            ->route('siswa.index')
            ->with('success', 'Data Siswa Berhasil Dihapus');
    }
}
```

## Data Models

### Model Optimization

Models will be refactored to focus solely on:
1. Defining relationships
2. Casting attributes
3. Providing lightweight query scopes
4. Defining fillable/guarded attributes

#### Optimized Model Example

```php
class Siswa extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;
    
    protected $table = 'siswa';
    
    protected $fillable = [
        'kelas_id',
        'wali_murid_user_id',
        'nisn',
        'nama_siswa',
        'nomor_hp_wali_murid',
    ];
    
    protected $casts = [
        'kelas_id' => 'integer',
        'wali_murid_user_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    // Relationships with return types
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }
    
    public function waliMurid(): BelongsTo
    {
        return $this->belongsTo(User::class, 'wali_murid_user_id');
    }
    
    public function riwayatPelanggaran(): HasMany
    {
        return $this->hasMany(RiwayatPelanggaran::class);
    }
    
    public function tindakLanjut(): HasMany
    {
        return $this->hasMany(TindakLanjut::class);
    }
    
    // Lightweight scopes only
    public function scopeInKelas(Builder $query, int $kelasId): Builder
    {
        return $query->where('kelas_id', $kelasId);
    }
    
    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('nama_siswa', 'like', "%{$keyword}%")
              ->orWhere('nisn', 'like', "%{$keyword}%");
        });
    }
}
```

### PHP Native Enums for Type Safety

The system will use PHP 8.1+ Native Enums for all status columns to ensure type safety and data consistency. Enums will be stored in `app/Enums/` directory.

#### Enum Definitions

**Note**: The enums shown below (StatusSiswa and TingkatPelanggaran) are examples. The actual enum values should be determined based on the real system requirements and existing database values.

```php
// app/Enums/StatusSiswa.php
namespace App\Enums;

enum StatusSiswa: string
{
    case AKTIF = 'aktif';
    case LULUS = 'lulus';
    case KELUAR = 'keluar';
    case DROPOUT = 'dropout';
    
    public function label(): string
    {
        return match($this) {
            self::AKTIF => 'Aktif',
            self::LULUS => 'Lulus',
            self::KELUAR => 'Keluar',
            self::DROPOUT => 'Dropout',
        };
    }
    
    public function color(): string
    {
        return match($this) {
            self::AKTIF => 'success',
            self::LULUS => 'info',
            self::KELUAR => 'warning',
            self::DROPOUT => 'danger',
        };
    }
}

// app/Enums/TingkatPelanggaran.php
namespace App\Enums;

enum TingkatPelanggaran: string
{
    case RINGAN = 'ringan';
    case SEDANG = 'sedang';
    case BERAT = 'berat';
    
    public function label(): string
    {
        return match($this) {
            self::RINGAN => 'Ringan',
            self::SEDANG => 'Sedang',
            self::BERAT => 'Berat',
        };
    }
    
    public function poinRange(): array
    {
        return match($this) {
            self::RINGAN => [1, 25],
            self::SEDANG => [26, 75],
            self::BERAT => [76, 100],
        };
    }
}
```

#### Model Integration with Enums

Models must cast enum attributes to their respective Enum classes:

```php
class Siswa extends Model
{
    protected $casts = [
        'status' => StatusSiswa::class,
        'kelas_id' => 'integer',
        'wali_murid_user_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}

class JenisPelanggaran extends Model
{
    protected $casts = [
        'tingkat' => TingkatPelanggaran::class,
        'poin' => 'integer',
    ];
}
```

#### Using Enums in Business Logic

```php
// Service layer - type-safe enum usage
class SiswaService
{
    public function activateSiswa(int $siswaId): SiswaData
    {
        $siswa = $this->siswaRepository->findOrFail($siswaId);
        $siswa->status = StatusSiswa::AKTIF; // Type-safe, no magic strings
        
        return $this->siswaRepository->update($siswaId, $siswa);
    }
    
    public function getSiswaByStatus(StatusSiswa $status): Collection
    {
        return $this->siswaRepository->findByStatus($status);
    }
}

// Repository layer - enum in queries
class SiswaRepository implements SiswaRepositoryInterface
{
    public function findByStatus(StatusSiswa $status): Collection
    {
        return $this->model
            ->where('status', $status->value) // Use ->value for database query
            ->get()
            ->map(fn($siswa) => SiswaData::from($siswa));
    }
}
```

#### DTO Integration with Enums

```php
class SiswaData extends Data
{
    public function __construct(
        public ?int $id,
        public string $nama_siswa,
        public string $nisn,
        public int $kelas_id,
        public StatusSiswa $status, // Type-safe enum property
        public ?int $wali_murid_user_id,
        public ?string $nomor_hp_wali_murid,
    ) {}
}

class JenisPelanggaranData extends Data
{
    public function __construct(
        public ?int $id,
        public string $nama,
        public string $kode,
        public TingkatPelanggaran $tingkat, // Type-safe enum property
        public int $poin,
        public ?string $deskripsi,
    ) {}
}
```

#### Benefits of Using Native Enums

1. **Type Safety**: Compiler catches invalid values at development time
2. **No Magic Strings**: Eliminates hardcoded strings scattered throughout codebase
3. **IDE Support**: Autocomplete and refactoring support
4. **Centralized Logic**: Helper methods (label, color) in one place
5. **Database Consistency**: Ensures only valid values are stored
6. **Refactoring Safety**: Changing enum values updates all usages

### Database Indexing Strategy

Indexes will be added to optimize query performance:

```php
// Migration: add_performance_indexes_to_siswa_table.php
Schema::table('siswa', function (Blueprint $table) {
    // Foreign key indexes
    $table->index('kelas_id');
    $table->index('wali_murid_user_id');
    
    // Search indexes
    $table->index('nisn');
    $table->index('nama_siswa');
    
    // Composite index for common queries
    $table->index(['kelas_id', 'nama_siswa']);
});

// Migration: add_performance_indexes_to_riwayat_pelanggaran_table.php
Schema::table('riwayat_pelanggaran', function (Blueprint $table) {
    // Foreign key indexes
    $table->index('siswa_id');
    $table->index('jenis_pelanggaran_id');
    $table->index('guru_pencatat_user_id');
    
    // Date range queries
    $table->index('tanggal_kejadian');
    
    // Composite indexes for common queries
    $table->index(['siswa_id', 'jenis_pelanggaran_id']);
    $table->index(['siswa_id', 'tanggal_kejadian']);
});
```


## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

Based on the prework analysis, many of the requirements are architectural constraints and code organization rules that cannot be tested as properties. However, we have identified several functional behaviors that can be validated through property-based testing:

### Property 1: Migration Reversibility

*For any* migration that affects data integrity, running the up method followed by the down method should restore the database schema to its original state.
**Validates: Requirements 1.5**

### Property 2: Model Relationship Type Safety

*For any* model relationship method, calling the method should return an instance of the declared return type (BelongsTo, HasMany, etc.).
**Validates: Requirements 2.2**

### Property 3: Attribute Casting Consistency

*For any* model attribute defined in the $casts array, retrieving that attribute should return a value of the specified cast type.
**Validates: Requirements 2.3**

### Property 4: DTO Validation Enforcement

*For any* DTO class, attempting to create an instance with invalid data should throw a validation exception with appropriate error messages.
**Validates: Requirements 3.2, 3.3**

### Property 5: DTO Data Transformation Consistency

*For any* valid data array, transforming it to a DTO and back to an array should preserve all data values and types.
**Validates: Requirements 3.4**

### Property 6: Repository Filtering Correctness

*For any* repository filter method with specific criteria, all returned results should match the filter criteria exactly.
**Validates: Requirements 4.3**

### Property 7: Service Layer Validation

*For any* service method that performs validation, invalid input should be rejected with clear error messages before any database operations occur.
**Validates: Requirements 5.3**

### Property 8: Service Cross-Domain Orchestration

*For any* service method that coordinates multiple repositories, either all operations should succeed or all should be rolled back (transaction atomicity).
**Validates: Requirements 5.4**

### Property 9: Controller Response Format Consistency

*For any* controller action, the HTTP response should have the correct status code and follow the standard response format (success/error structure).
**Validates: Requirements 6.4**

### Property 10: Route Middleware Application

*For any* route defined in the system, the route should have the appropriate middleware applied based on its access requirements.
**Validates: Requirements 7.2**

### Property 11: Form Request Validation Rules

*For any* Form Request class, submitting invalid data should return validation errors that match the defined rules.
**Validates: Requirements 8.2**

### Property 12: Form Request Authorization

*For any* Form Request with authorization logic, unauthorized users should receive a 403 response before validation occurs.
**Validates: Requirements 8.3**

### Property 13: Eager Loading Prevention of N+1

*For any* repository method that loads related data, the method should use eager loading and execute a predictable number of queries regardless of result set size.
**Validates: Requirements 9.1, 9.4, 9.5**

### Property 14: Query Column Selection

*For any* repository method that retrieves data, the query should select only the columns needed for the operation instead of SELECT *.
**Validates: Requirements 9.2**

### Property 15: Pagination Implementation

*For any* list endpoint, the response should include pagination metadata (current page, total pages, per page count).
**Validates: Requirements 9.3**

### Property 16: Foreign Key Index Existence

*For any* foreign key column in the database, an index should exist on that column to optimize join operations.
**Validates: Requirements 10.1**

### Property 17: Frequently Queried Column Indexes

*For any* column used in WHERE clauses or ORDER BY clauses, an appropriate index should exist.
**Validates: Requirements 10.2, 10.3**

### Property 18: Composite Index for Searches

*For any* multi-column search operation, a composite index should exist covering those columns.
**Validates: Requirements 10.4**

### Property 19: Cache Usage for Static Data

*For any* query for static reference data (roles, categories, etc.), the result should be cached and subsequent calls should hit the cache.
**Validates: Requirements 11.1**

### Property 20: Cache Invalidation on Data Change

*For any* data modification operation, related cache entries should be invalidated to prevent stale data.
**Validates: Requirements 11.2**

### Property 21: Cache Key Consistency

*For any* cached data, the cache key should follow a consistent naming pattern that includes the resource type and identifier.
**Validates: Requirements 11.3**

### Property 22: Cache Tag Grouping

*For any* related cache entries, they should use cache tags to enable grouped invalidation.
**Validates: Requirements 11.5**

### Property 23: File Upload Storage

*For any* file upload operation, the file should be stored using Laravel's storage system with proper disk configuration.
**Validates: Requirements 12.1**

### Property 24: File Upload Validation

*For any* file upload, the system should validate file type, size, and mime type before accepting the upload.
**Validates: Requirements 12.3**

### Property 25: File Cleanup on Record Deletion

*For any* record deletion that has associated files, the files should be removed from storage.
**Validates: Requirements 12.4**

### Property 26: File Organization Structure

*For any* uploaded file, it should be stored in a directory structure organized by type and date.
**Validates: Requirements 12.5**

### Property 27: Factory Data Validity

*For any* model factory, generated instances should pass all model validation rules and have valid relationships.
**Validates: Requirements 15.3**

### Property 28: Seeder Idempotency

*For any* seeder class, running the seeder multiple times should not create duplicate data or cause errors.
**Validates: Requirements 15.4**

### Property 29: Policy Authorization Checks

*For any* policy method, unauthorized users should be denied access with appropriate error messages.
**Validates: Requirements 16.2, 16.4**

### Property 30: Exception Logging Context

*For any* exception that occurs, the system should log the exception with appropriate context (user, request, stack trace).
**Validates: Requirements 17.1**

### Property 31: Log Channel Routing

*For any* log entry, it should be written to the appropriate channel based on log type and severity.
**Validates: Requirements 17.3**

### Property 32: User-Friendly Error Messages

*For any* exception shown to users, the error message should be user-friendly while technical details are logged separately.
**Validates: Requirements 17.4**

### Property 33: Email Queueing

*For any* email sending operation, the email should be queued for asynchronous processing instead of sent synchronously.
**Validates: Requirements 18.1**

### Property 34: File Processing Queueing

*For any* large file processing operation, the processing should be dispatched to a queue job.
**Validates: Requirements 18.2**

### Property 35: Bulk Operation Queueing

*For any* bulk operation affecting multiple records, the operation should be dispatched to a queue job.
**Validates: Requirements 18.3**

### Property 36: Job Retry Logic

*For any* queue job, failed jobs should be retried according to configured retry logic before being marked as failed.
**Validates: Requirements 18.4**

### Property 37: Blade Component Type Safety

*For any* Blade component with typed properties, passing data of incorrect type should result in a type error.
**Validates: Requirements 19.3**

### Property 38: Enum Type Safety in Models

*For any* model with enum-casted attributes, retrieving that attribute should return an instance of the declared Enum class.
**Validates: Requirements 21.2**

### Property 39: Enum Value Consistency

*For any* enum value stored in the database, the value should match one of the defined enum cases.
**Validates: Requirements 21.1**

### Property 40: No Magic Strings in Business Logic

*For any* service or repository method that uses status values, the method should use Enum types instead of string literals.
**Validates: Requirements 21.3**

## Error Handling

### Exception Hierarchy

The system will implement a custom exception hierarchy for domain-specific errors:

```php
// Base exception
abstract class DomainException extends Exception
{
    abstract public function getUserMessage(): string;
    abstract public function getLogContext(): array;
}

// Specific exceptions
class SiswaNotFoundException extends DomainException
{
    public function getUserMessage(): string
    {
        return 'Data siswa tidak ditemukan.';
    }
    
    public function getLogContext(): array
    {
        return ['siswa_id' => $this->siswaId];
    }
}

class UnauthorizedException extends DomainException
{
    public function getUserMessage(): string
    {
        return 'Anda tidak memiliki akses untuk melakukan operasi ini.';
    }
    
    public function getLogContext(): array
    {
        return [
            'user_id' => auth()->id(),
            'attempted_action' => $this->action,
        ];
    }
}

class ValidationException extends DomainException
{
    public function getUserMessage(): string
    {
        return 'Data yang Anda masukkan tidak valid.';
    }
    
    public function getLogContext(): array
    {
        return ['validation_errors' => $this->errors];
    }
}
```

### Global Exception Handler

```php
// app/Exceptions/Handler.php
class Handler extends ExceptionHandler
{
    public function register(): void
    {
        $this->reportable(function (DomainException $e) {
            Log::error($e->getMessage(), array_merge([
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], $e->getLogContext()));
        });
        
        $this->renderable(function (DomainException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getUserMessage(),
                ], 400);
            }
            
            return back()->with('error', $e->getUserMessage());
        });
    }
}
```

### Logging Strategy

```php
// config/logging.php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['daily', 'slack'],
        'ignore_exceptions' => false,
    ],
    
    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
        'days' => 14,
    ],
    
    'slack' => [
        'driver' => 'slack',
        'url' => env('LOG_SLACK_WEBHOOK_URL'),
        'username' => 'Laravel Log',
        'emoji' => ':boom:',
        'level' => 'critical',
    ],
    
    'audit' => [
        'driver' => 'daily',
        'path' => storage_path('logs/audit.log'),
        'level' => 'info',
        'days' => 90,
    ],
    
    'performance' => [
        'driver' => 'daily',
        'path' => storage_path('logs/performance.log'),
        'level' => 'info',
        'days' => 30,
    ],
];
```

## Testing Strategy

### Dual Testing Approach

The refactored system will implement both unit testing and property-based testing to ensure comprehensive coverage:

#### Unit Testing

Unit tests will verify specific examples, edge cases, and integration points:

**Test Categories:**
1. **Repository Tests**: Verify CRUD operations, filtering, and query building
2. **Service Tests**: Verify business logic, orchestration, and transaction handling
3. **DTO Tests**: Verify data transformation and validation
4. **Controller Tests**: Verify HTTP request/response handling
5. **Policy Tests**: Verify authorization logic
6. **Job Tests**: Verify queue job execution and retry logic

**Example Unit Test:**

```php
class SiswaRepositoryTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_find_by_nisn_returns_siswa_when_exists()
    {
        $siswa = Siswa::factory()->create(['nisn' => '1234567890']);
        $repository = app(SiswaRepositoryInterface::class);
        
        $result = $repository->findByNisn('1234567890');
        
        $this->assertInstanceOf(SiswaData::class, $result);
        $this->assertEquals('1234567890', $result->nisn);
    }
    
    public function test_find_by_nisn_returns_null_when_not_exists()
    {
        $repository = app(SiswaRepositoryInterface::class);
        
        $result = $repository->findByNisn('9999999999');
        
        $this->assertNull($result);
    }
}
```

#### Property-Based Testing

Property-based tests will verify universal properties across all inputs using a PBT library. For PHP/Laravel, we'll use **Pest with Property Testing** or **PHPUnit with QuickCheck**.

**Property Testing Library**: Pest PHP with Property Testing plugin

**Configuration**: Each property test should run a minimum of 100 iterations to ensure thorough coverage.

**Test Tagging**: Each property-based test MUST be tagged with a comment explicitly referencing the correctness property from this design document using the format:
```php
/**
 * Feature: backend-clean-architecture, Property 13: Eager Loading Prevention of N+1
 */
```

**Example Property Test:**

```php
use function Pest\Faker\fake;

/**
 * Feature: backend-clean-architecture, Property 13: Eager Loading Prevention of N+1
 */
it('prevents N+1 queries when loading siswa with relationships', function () {
    // Arrange: Create random number of siswa with relationships
    $count = fake()->numberBetween(5, 20);
    Siswa::factory()->count($count)->create();
    
    $repository = app(SiswaRepositoryInterface::class);
    
    // Act: Load all siswa with relationships
    DB::enableQueryLog();
    $result = $repository->getAllWithRelationships();
    $queries = DB::getQueryLog();
    DB::disableQueryLog();
    
    // Assert: Should execute fixed number of queries regardless of count
    // Expected: 1 query for siswa + 1 for kelas + 1 for jurusan = 3 queries
    expect($queries)->toHaveCount(3);
})->repeat(100);

/**
 * Feature: backend-clean-architecture, Property 6: Repository Filtering Correctness
 */
it('filters siswa by kelas correctly', function () {
    // Arrange: Create random siswa in different kelas
    $kelas1 = Kelas::factory()->create();
    $kelas2 = Kelas::factory()->create();
    
    $siswaKelas1Count = fake()->numberBetween(3, 10);
    $siswaKelas2Count = fake()->numberBetween(3, 10);
    
    Siswa::factory()->count($siswaKelas1Count)->create(['kelas_id' => $kelas1->id]);
    Siswa::factory()->count($siswaKelas2Count)->create(['kelas_id' => $kelas2->id]);
    
    $repository = app(SiswaRepositoryInterface::class);
    
    // Act: Filter by kelas1
    $result = $repository->findByKelas($kelas1->id);
    
    // Assert: All results should belong to kelas1
    expect($result)->toHaveCount($siswaKelas1Count);
    expect($result->every(fn($siswa) => $siswa->kelas_id === $kelas1->id))->toBeTrue();
})->repeat(100);

/**
 * Feature: backend-clean-architecture, Property 5: DTO Data Transformation Consistency
 */
it('preserves data through DTO transformation', function () {
    // Arrange: Generate random valid siswa data
    $data = [
        'id' => fake()->numberBetween(1, 1000),
        'nama_siswa' => fake()->name(),
        'nisn' => fake()->numerify('##########'),
        'kelas_id' => fake()->numberBetween(1, 50),
        'status' => fake()->randomElement(['aktif', 'lulus', 'keluar', 'dropout']),
        'wali_murid_user_id' => fake()->optional()->numberBetween(1, 100),
        'nomor_hp_wali_murid' => fake()->optional()->numerify('08##########'),
    ];
    
    // Act: Transform to DTO and back
    $dto = SiswaData::from($data);
    $result = $dto->toArray();
    
    // Assert: All data should be preserved
    expect($result)->toEqual($data);
})->repeat(100);

/**
 * Feature: backend-clean-architecture, Property 38: Enum Type Safety in Models
 */
it('returns enum instance when retrieving enum-casted attribute', function () {
    // Arrange: Create siswa with random status
    $status = fake()->randomElement(['aktif', 'lulus', 'keluar', 'dropout']);
    $siswa = Siswa::factory()->create(['status' => $status]);
    
    // Act: Retrieve the model
    $retrieved = Siswa::find($siswa->id);
    
    // Assert: Status should be StatusSiswa enum instance
    expect($retrieved->status)->toBeInstanceOf(StatusSiswa::class);
    expect($retrieved->status->value)->toBe($status);
})->repeat(100);

/**
 * Feature: backend-clean-architecture, Property 40: No Magic Strings in Business Logic
 */
it('uses enum types instead of string literals in service methods', function () {
    // Arrange: Create service and repository
    $service = app(SiswaService::class);
    $status = fake()->randomElement(StatusSiswa::cases());
    
    // Act: Call service method with enum
    $result = $service->getSiswaByStatus($status);
    
    // Assert: All returned siswa should have the specified status
    expect($result->every(fn($siswa) => $siswa->status === $status))->toBeTrue();
})->repeat(100);
```

### Integration Testing

Integration tests will verify that multiple components work together correctly:

```php
class SiswaCreationIntegrationTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_creating_siswa_with_wali_murid_account()
    {
        $this->actingAs(User::factory()->operator()->create());
        
        $response = $this->post(route('siswa.store'), [
            'nisn' => '1234567890',
            'nama_siswa' => 'Test Siswa',
            'kelas_id' => Kelas::factory()->create()->id,
            'create_wali' => true,
        ]);
        
        $response->assertRedirect(route('siswa.index'));
        $this->assertDatabaseHas('siswa', ['nisn' => '1234567890']);
        $this->assertDatabaseHas('users', ['nama' => 'Wali dari Test Siswa']);
    }
}
```

### Performance Testing

Performance tests will verify query optimization and caching:

```php
class PerformanceTest extends TestCase
{
    public function test_siswa_list_query_performance()
    {
        Siswa::factory()->count(1000)->create();
        
        $startTime = microtime(true);
        $startQueries = count(DB::getQueryLog());
        
        $repository = app(SiswaRepositoryInterface::class);
        $result = $repository->filterAndPaginate(new FilterSiswaRequest());
        
        $endTime = microtime(true);
        $endQueries = count(DB::getQueryLog());
        
        $executionTime = ($endTime - $startTime) * 1000; // Convert to ms
        $queryCount = $endQueries - $startQueries;
        
        // Assert: Should complete in under 100ms with less than 5 queries
        $this->assertLessThan(100, $executionTime);
        $this->assertLessThan(5, $queryCount);
    }
}
```

## Implementation Phases

The refactoring will be implemented in phases to minimize risk and ensure incremental progress:

### Phase 1: Foundation (Weeks 1-2)
- Set up folder structure for DTOs, Repositories, Services, Policies
- Install and configure Laravel Data package
- Create base repository interface and implementation
- Set up property testing framework
- Create migration for missing database indexes

### Phase 2: Core Domain - Siswa Module (Weeks 3-4)
- Create SiswaData DTO with validation
- Implement SiswaRepository with interface
- Implement SiswaService with business logic
- Create Form Requests for Siswa operations
- Refactor SiswaController to use service layer
- Write unit and property tests for Siswa module

### Phase 3: Pelanggaran Module (Weeks 5-6)
- Create DTOs for RiwayatPelanggaran, JenisPelanggaran
- Implement repositories for pelanggaran domain
- Refactor PelanggaranRulesEngine service
- Create Form Requests for pelanggaran operations
- Refactor controllers to use service layer
- Write unit and property tests

### Phase 4: Tindak Lanjut Module (Week 7)
- Create DTOs for TindakLanjut, SuratPanggilan
- Implement repositories
- Refactor services
- Create Form Requests
- Refactor controllers
- Write tests

### Phase 5: User & Authentication (Week 8)
- Create UserData DTO
- Implement UserRepository
- Implement UserService
- Create authorization policies
- Refactor UserController
- Write tests

### Phase 6: Caching & Performance (Week 9)
- Implement caching in repositories
- Add cache invalidation in services
- Optimize queries with eager loading
- Add query result caching for static data
- Performance testing and optimization

### Phase 7: Queue Jobs (Week 10)
- Create queue jobs for email notifications
- Create queue jobs for bulk operations
- Implement job retry logic
- Configure queue workers
- Write job tests

### Phase 8: Route Organization (Week 11)
- Split routes into domain-specific files
- Organize route groups with middleware
- Update route naming conventions
- Create route documentation

### Phase 9: Blade Components (Week 12)
- Create reusable Blade components
- Refactor views to use components
- Implement typed component properties
- Organize components by domain

### Phase 10: Final Testing & Documentation (Week 13)
- Run full test suite
- Performance benchmarking
- Code quality checks with Pint
- Update documentation
- Deployment preparation

## Folder Structure

The refactored application will follow this folder structure:

```
app/
├── Console/
│   └── Commands/
├── Data/                          # DTOs
│   ├── Siswa/
│   │   ├── SiswaData.php
│   │   ├── SiswaFilterData.php
│   │   └── SiswaDetailResponse.php
│   ├── Pelanggaran/
│   │   ├── RiwayatPelanggaranData.php
│   │   ├── RiwayatPelanggaranFilterData.php
│   │   └── CatatPelanggaranRequest.php
│   └── User/
│       └── UserData.php
├── Enums/                         # PHP Native Enums
│   ├── StatusSiswa.php
│   ├── TingkatPelanggaran.php
│   └── StatusTindakLanjut.php
├── Exceptions/
│   ├── DomainException.php
│   ├── SiswaNotFoundException.php
│   └── UnauthorizedException.php
├── Http/
│   ├── Controllers/
│   │   ├── Siswa/
│   │   │   └── SiswaController.php
│   │   ├── Pelanggaran/
│   │   │   ├── PelanggaranController.php
│   │   │   └── RiwayatController.php
│   │   └── User/
│   │       └── UserController.php
│   ├── Middleware/
│   └── Requests/
│       ├── Siswa/
│       │   ├── CreateSiswaRequest.php
│       │   └── UpdateSiswaRequest.php
│       └── Pelanggaran/
│           └── CatatPelanggaranRequest.php
├── Jobs/
│   ├── SendNotificationEmail.php
│   └── ProcessBulkSiswaImport.php
├── Models/
│   ├── Siswa.php
│   ├── RiwayatPelanggaran.php
│   └── User.php
├── Policies/
│   ├── SiswaPolicy.php
│   └── RiwayatPelanggaranPolicy.php
├── Repositories/
│   ├── Contracts/
│   │   ├── BaseRepositoryInterface.php
│   │   ├── SiswaRepositoryInterface.php
│   │   └── RiwayatPelanggaranRepositoryInterface.php
│   ├── BaseRepository.php
│   ├── SiswaRepository.php
│   └── RiwayatPelanggaranRepository.php
├── Services/
│   ├── Siswa/
│   │   └── SiswaService.php
│   ├── Pelanggaran/
│   │   ├── PelanggaranService.php
│   │   └── PelanggaranRulesEngine.php
│   └── User/
│       ├── UserService.php
│       └── UserNamingService.php
└── Providers/
    ├── AppServiceProvider.php
    ├── RepositoryServiceProvider.php
    └── AuthServiceProvider.php

routes/
├── web.php                        # Main routes file
├── siswa.php                      # Siswa domain routes
├── pelanggaran.php                # Pelanggaran domain routes
├── user.php                       # User management routes
└── dashboard.php                  # Dashboard routes

tests/
├── Unit/
│   ├── Repositories/
│   │   └── SiswaRepositoryTest.php
│   ├── Services/
│   │   └── SiswaServiceTest.php
│   └── Data/
│       └── SiswaDataTest.php
├── Feature/
│   ├── Siswa/
│   │   └── SiswaManagementTest.php
│   └── Pelanggaran/
│       └── PelanggaranRecordingTest.php
└── Property/
    ├── RepositoryPropertyTest.php
    ├── DTOPropertyTest.php
    └── CachingPropertyTest.php
```

## Migration Strategy

### Database Migration Cleanup

Existing migrations will be analyzed and potentially consolidated:

1. **Audit Current Migrations**: Review all 38 existing migrations
2. **Identify Consolidation Opportunities**: Merge related migrations where appropriate
3. **Create Index Migrations**: Add missing indexes in new migration files
4. **Document Migration History**: Create migration documentation

### Code Migration Approach

1. **Feature Flags**: Use feature flags to gradually roll out refactored code
2. **Parallel Implementation**: Keep old code working while building new architecture
3. **Gradual Cutover**: Switch modules one at a time to new architecture
4. **Rollback Plan**: Maintain ability to rollback to old code if issues arise

### Data Migration

No data migration required as this is a code refactoring, not a schema change.

## Performance Benchmarks

Target performance metrics after refactoring:

| Metric | Current | Target | Improvement |
|--------|---------|--------|-------------|
| Siswa List Page Load | ~500ms | <100ms | 5x faster |
| Riwayat List with Filters | ~800ms | <150ms | 5x faster |
| Dashboard Load | ~1200ms | <200ms | 6x faster |
| Query Count (Siswa List) | 15-20 | <5 | 75% reduction |
| Memory Usage | ~50MB | <30MB | 40% reduction |
| Cache Hit Rate | 0% | >80% | New capability |

## Monitoring and Observability

### Performance Monitoring

```php
// Middleware to log slow queries
class LogSlowQueries
{
    public function handle($request, Closure $next)
    {
        DB::listen(function ($query) {
            if ($query->time > 100) { // Log queries over 100ms
                Log::channel('performance')->warning('Slow query detected', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time,
                ]);
            }
        });
        
        return $next($request);
    }
}
```

### Cache Monitoring

```php
// Track cache hit/miss rates
class CacheMonitor
{
    public static function recordHit(string $key): void
    {
        Cache::increment('cache_hits');
        Log::channel('performance')->info('Cache hit', ['key' => $key]);
    }
    
    public static function recordMiss(string $key): void
    {
        Cache::increment('cache_misses');
        Log::channel('performance')->info('Cache miss', ['key' => $key]);
    }
    
    public static function getHitRate(): float
    {
        $hits = Cache::get('cache_hits', 0);
        $misses = Cache::get('cache_misses', 0);
        $total = $hits + $misses;
        
        return $total > 0 ? ($hits / $total) * 100 : 0;
    }
}
```

## Security Considerations

### Input Validation

All user input will be validated through:
1. Form Request validation rules
2. DTO validation
3. Service layer business rule validation

### Authorization

Authorization will be enforced at multiple layers:
1. Route middleware for role-based access
2. Form Request authorize() methods
3. Policy classes for resource-level authorization
4. Service layer for business rule authorization

### SQL Injection Prevention

- Use Eloquent ORM and query builder (parameterized queries)
- Never use raw SQL with user input
- Validate and sanitize all input data

### File Upload Security

- Validate file types and mime types
- Limit file sizes
- Store files outside public directory
- Generate unique filenames
- Scan uploaded files for malware (future enhancement)

## Conclusion

This design document provides a comprehensive blueprint for refactoring the SMKN1 Disciplinary System backend to implement clean architecture principles and Laravel best practices. The refactoring will result in a more maintainable, testable, performant, and scalable system that follows industry standards and best practices.

The implementation will be done incrementally over 13 weeks, with each phase building upon the previous one. The dual testing approach (unit tests + property-based tests) will ensure comprehensive coverage and confidence in the refactored code.

Key benefits of this refactoring:
- **Maintainability**: Clear separation of concerns and consistent patterns
- **Testability**: Well-defined boundaries enable comprehensive testing
- **Performance**: Optimized queries, caching, and proper indexing
- **Scalability**: Repository pattern allows easy database switching
- **Type Safety**: DTOs ensure predictable data structures
- **Developer Experience**: Consistent patterns and clear documentation
