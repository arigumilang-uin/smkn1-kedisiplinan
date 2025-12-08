# Implementation Plan: Backend Clean Architecture Refactoring

## Overview

This implementation plan breaks down the backend refactoring into discrete, manageable coding tasks. Each task builds incrementally on previous steps, following clean architecture principles with strict HTTP decoupling, DTO-based data flow, and PHP Native Enums for type safety.

**Key Principles:**
- Services NEVER receive HTTP Request objects, only DTOs
- Controllers convert Form Requests to DTOs before calling Services
- Repositories receive Filter DTOs, not Form Requests
- Use PHP Native Enums for all status columns

---

## Phase 1: Foundation Setup

- [ ] 1. Set up project structure and install dependencies
  - Create folder structure: `app/Data`, `app/Enums`, `app/Repositories`, `app/Services`
  - Install Spatie Laravel Data package: `composer require spatie/laravel-data`
  - Configure Laravel Pint for PSR-12 standards
  - Set up property testing framework (Pest with Property Testing plugin)
  - _Requirements: 13.1, 13.2, 20.3_

- [ ] 2. Create base repository infrastructure
  - Create `app/Repositories/Contracts/BaseRepositoryInterface.php`
  - Implement `app/Repositories/BaseRepository.php` with CRUD methods
  - Create `app/Providers/RepositoryServiceProvider.php` for binding interfaces
  - Register RepositoryServiceProvider in `config/app.php`
  - _Requirements: 4.2, 4.5_

- [ ]* 2.1 Write unit tests for BaseRepository
  - Test find, findOrFail, create, update, delete methods
  - Test error handling for non-existent records
  - _Requirements: 4.1_

---

## Phase 2: PHP Native Enums

- [ ] 3. Create PHP Native Enums for status columns
  - Create `app/Enums/StatusSiswa.php` with cases based on existing database values
  - Create `app/Enums/TingkatPelanggaran.php` with cases based on existing database values
  - Create `app/Enums/StatusTindakLanjut.php` if applicable
  - Add helper methods (label(), color(), etc.) to each enum
  - _Requirements: 21.1_

- [ ]* 3.1 Write property test for enum type safety
  - **Property 38: Enum Type Safety in Models**
  - **Validates: Requirements 21.2**

- [ ] 4. Update models to use enum casting
  - Update `Siswa` model: cast `status` to `StatusSiswa::class`
  - Update `JenisPelanggaran` model: cast `tingkat` to `TingkatPelanggaran::class`
  - Update `TindakLanjut` model: cast status column to appropriate enum
  - Remove any hardcoded status strings from model methods
  - _Requirements: 21.2, 2.3_

- [ ]* 4.1 Write property test for enum value consistency
  - **Property 39: Enum Value Consistency**
  - **Validates: Requirements 21.1**

---

## Phase 3: Core DTOs

- [ ] 5. Create entity DTOs for core domain models
  - Create `app/Data/Siswa/SiswaData.php` with all properties and validation
  - Create `app/Data/Pelanggaran/RiwayatPelanggaranData.php`
  - Create `app/Data/Pelanggaran/JenisPelanggaranData.php`
  - Create `app/Data/TindakLanjut/TindakLanjutData.php`
  - Create `app/Data/User/UserData.php`
  - Include enum properties (e.g., `public StatusSiswa $status`)
  - _Requirements: 3.1, 3.2, 3.5_

- [ ]* 5.1 Write property test for DTO transformation consistency
  - **Property 5: DTO Data Transformation Consistency**
  - **Validates: Requirements 3.4**

- [ ] 6. Create filter DTOs for repository queries
  - Create `app/Data/Siswa/SiswaFilterData.php` with search, kelas_id, status, etc.
  - Create `app/Data/Pelanggaran/RiwayatPelanggaranFilterData.php`
  - Create `app/Data/TindakLanjut/TindakLanjutFilterData.php`
  - Include pagination properties (perPage, sortBy, sortDirection)
  - _Requirements: 3.1, 4.3_

- [ ]* 6.1 Write property test for DTO validation enforcement
  - **Property 4: DTO Validation Enforcement**
  - **Validates: Requirements 3.2, 3.3**

---

## Phase 4: Core Repositories (Siswa & User Foundation)

- [ ] 7. Implement User repository foundation (required for SiswaService)
  - Create `app/Data/User/UserData.php` with all properties
  - Create `app/Repositories/Contracts/UserRepositoryInterface.php`
  - Implement `app/Repositories/UserRepository.php`
  - Implement basic CRUD methods: find, create, update, delete
  - Implement `findByUsername(string $username): ?UserData`
  - This is needed because SiswaService will create Wali Murid accounts
  - _Requirements: 4.1, 4.3_

- [ ] 8. Implement Siswa repository with interface
  - Create `app/Repositories/Contracts/SiswaRepositoryInterface.php`
  - Implement `app/Repositories/SiswaRepository.php`
  - Implement methods: findByNisn, findByKelas, findByStatus, filterAndPaginate
  - Use `SiswaFilterData` for filtering (NOT Form Request)
  - Apply eager loading for relationships
  - Select only required columns
  - _Requirements: 4.1, 4.3, 9.1, 9.2_

- [ ]* 8.1 Write property test for repository filtering correctness
  - **Property 6: Repository Filtering Correctness**
  - **Validates: Requirements 4.3**

- [ ]* 8.2 Write property test for eager loading N+1 prevention
  - **Property 13: Eager Loading Prevention of N+1**
  - **Validates: Requirements 9.1, 9.4, 9.5**

- [ ]* 8.3 Write property test for query column selection
  - **Property 14: Query Column Selection**
  - **Validates: Requirements 9.2**

- [ ] 9. Bind repositories in service provider
  - Add binding in `RepositoryServiceProvider`: `SiswaRepositoryInterface::class => SiswaRepository::class`
  - Add binding: `UserRepositoryInterface::class => UserRepository::class`
  - _Requirements: 4.5_

---

## Phase 5: Siswa Module - Service Layer

- [ ] 10. Implement UserNamingService (helper for SiswaService)
  - Create `app/Services/User/UserNamingService.php`
  - Implement `generateWaliMuridUsername(string $nisn): string`
  - Implement `generateWaliMuridPassword(string $nisn): string`
  - This service will be used by SiswaService to create Wali Murid accounts
  - _Requirements: 5.1_

- [ ] 11. Implement Siswa service with business logic
  - Create `app/Services/Siswa/SiswaService.php`
  - Inject `SiswaRepositoryInterface`, `UserRepositoryInterface`, `UserNamingService`
  - Implement `createSiswa(SiswaData $data, bool $createWali): SiswaData`
  - Implement `updateSiswa(int $id, SiswaData $data, int $userId, string $role): SiswaData`
  - Implement `deleteSiswa(int $id): bool`
  - Implement `getFilteredSiswa(SiswaFilterData $filters): LengthAwarePaginator`
  - Implement `getSiswaByStatus(StatusSiswa $status): Collection`
  - Use DB transactions for multi-step operations
  - Inject only repositories and other services (NO Request objects)
  - _Requirements: 5.1, 5.2, 5.5, 5.6, 21.3_

- [ ]* 11.1 Write property test for service validation
  - **Property 7: Service Layer Validation**
  - **Validates: Requirements 5.3**

- [ ]* 11.2 Write property test for transaction atomicity
  - **Property 8: Service Cross-Domain Orchestration**
  - **Validates: Requirements 5.4**

- [ ]* 11.3 Write property test for no magic strings in business logic
  - **Property 40: No Magic Strings in Business Logic**
  - **Validates: Requirements 21.3**

---

## Phase 6: Siswa Module - HTTP Layer

- [ ] 12. Create Form Requests for Siswa operations
  - Create `app/Http/Requests/Siswa/CreateSiswaRequest.php` with validation rules
  - Create `app/Http/Requests/Siswa/UpdateSiswaRequest.php` with role-based rules
  - Create `app/Http/Requests/Siswa/FilterSiswaRequest.php` for filtering
  - Implement authorize() methods with policy checks
  - Add custom error messages
  - _Requirements: 8.1, 8.2, 8.3, 8.4_

- [ ]* 12.1 Write property test for Form Request validation
  - **Property 11: Form Request Validation Rules**
  - **Validates: Requirements 8.2**

- [ ]* 12.2 Write property test for Form Request authorization
  - **Property 12: Form Request Authorization**
  - **Validates: Requirements 8.3**

- [ ] 13. Refactor SiswaController to use service layer
  - Inject `SiswaService` via constructor
  - In `index()`: Convert `FilterSiswaRequest` to `SiswaFilterData`, call service
  - In `store()`: Convert `CreateSiswaRequest` to `SiswaData`, call service
  - In `update()`: Convert `UpdateSiswaRequest` to `SiswaData`, pass userId and role
  - Keep all methods under 20 lines
  - Return consistent response formats
  - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5_

- [ ]* 13.1 Write property test for controller response format
  - **Property 9: Controller Response Format Consistency**
  - **Validates: Requirements 6.4**

---

## Phase 7: Database Optimization for Siswa

- [ ] 14. Create migration for Siswa table indexes
  - Create migration: `add_performance_indexes_to_siswa_table`
  - Add index on `kelas_id` (foreign key)
  - Add index on `wali_murid_user_id` (foreign key)
  - Add index on `nisn` (frequently queried)
  - Add index on `nama_siswa` (search)
  - Add index on `status` (filtering)
  - Add composite index on `['kelas_id', 'status']`
  - _Requirements: 10.1, 10.2, 10.3, 10.4_

- [ ]* 14.1 Write property test for foreign key index existence
  - **Property 16: Foreign Key Index Existence**
  - **Validates: Requirements 10.1**

- [ ]* 14.2 Write property test for frequently queried column indexes
  - **Property 17: Frequently Queried Column Indexes**
  - **Validates: Requirements 10.2, 10.3**

---

## Phase 8: Checkpoint - Siswa Module Complete

- [ ] 15. Ensure all tests pass for Siswa module
  - Run all unit tests for Siswa repository, service, controller
  - Run all property tests
  - Verify no N+1 queries in list endpoints
  - Check code formatting with Laravel Pint
  - Ensure all tests pass, ask the user if questions arise

---

## Phase 9: Pelanggaran Module - Repository Layer

- [ ] 16. Implement Pelanggaran repositories
  - Create `app/Repositories/Contracts/RiwayatPelanggaranRepositoryInterface.php`
  - Implement `app/Repositories/RiwayatPelanggaranRepository.php`
  - Create `app/Repositories/Contracts/JenisPelanggaranRepositoryInterface.php`
  - Implement `app/Repositories/JenisPelanggaranRepository.php`
  - Implement filterAndPaginate with `RiwayatPelanggaranFilterData`
  - Implement methods: findBySiswa, findByDateRange, getTotalPoinBySiswa
  - Use eager loading for siswa, jenisPelanggaran, guruPencatat relationships
  - _Requirements: 4.1, 4.3, 9.1, 9.2_

- [ ]* 16.1 Write property test for pelanggaran filtering
  - **Property 6: Repository Filtering Correctness**
  - **Validates: Requirements 4.3**

- [ ] 17. Bind Pelanggaran repositories in service provider
  - Add bindings in `RepositoryServiceProvider`
  - _Requirements: 4.5_

---

## Phase 10: Pelanggaran Module - Service Layer

- [ ] 18. Implement Pelanggaran service with business logic
  - Create `app/Services/Pelanggaran/PelanggaranService.php`
  - Implement `catatPelanggaran(RiwayatPelanggaranData $data): RiwayatPelanggaranData`
  - Implement `updatePelanggaran(int $id, RiwayatPelanggaranData $data): RiwayatPelanggaranData`
  - Implement `getFilteredRiwayat(RiwayatPelanggaranFilterData $filters): LengthAwarePaginator`
  - Implement `calculateTotalPoin(int $siswaId): int`
  - Use enum `TingkatPelanggaran` for severity checks
  - Inject repositories only (NO Request objects)
  - _Requirements: 5.1, 5.2, 5.6, 21.3_

- [ ]* 18.1 Write unit tests for pelanggaran business logic
  - Test poin calculation
  - Test tingkat pelanggaran categorization
  - _Requirements: 5.1_

---

## Phase 11: Pelanggaran Module - HTTP Layer

- [ ] 19. Create Form Requests for Pelanggaran operations
  - Create `app/Http/Requests/Pelanggaran/CatatPelanggaranRequest.php`
  - Create `app/Http/Requests/Pelanggaran/UpdatePelanggaranRequest.php`
  - Create `app/Http/Requests/Pelanggaran/FilterRiwayatRequest.php`
  - Implement authorization with policies
  - _Requirements: 8.1, 8.2, 8.3_

- [ ] 20. Refactor Pelanggaran controllers
  - Refactor `RiwayatPelanggaranController` to use service
  - Convert Form Requests to DTOs before calling service
  - Keep methods under 20 lines
  - _Requirements: 6.1, 6.2, 6.3, 6.5_

---

## Phase 12: Database Optimization for Pelanggaran

- [ ] 21. Create migration for Pelanggaran indexes
  - Create migration: `add_performance_indexes_to_riwayat_pelanggaran_table`
  - Add indexes on foreign keys: siswa_id, jenis_pelanggaran_id, guru_pencatat_user_id
  - Add index on `tanggal_kejadian` (date range queries)
  - Add composite indexes: `['siswa_id', 'jenis_pelanggaran_id']`, `['siswa_id', 'tanggal_kejadian']`
  - _Requirements: 10.1, 10.2, 10.4_

---

## Phase 13: Tindak Lanjut Module

- [ ] 22. Implement Tindak Lanjut repository and service
  - Create `TindakLanjutRepositoryInterface` and implementation
  - Create `TindakLanjutService` with business logic
  - Use `TindakLanjutFilterData` for filtering
  - Use enum for status if applicable
  - _Requirements: 4.1, 5.1, 5.6_

- [ ] 23. Create Form Requests and refactor controller
  - Create Form Requests for Tindak Lanjut operations
  - Refactor `TindakLanjutController` to use service
  - Convert requests to DTOs
  - _Requirements: 6.1, 6.2, 8.1_

---

## Phase 14: User & Authentication Module

- [ ] 24. Complete User service implementation
  - Expand `UserService` with full user management logic
  - Implement user CRUD operations
  - Implement role assignment logic
  - Use DTOs for all data transfer
  - _Requirements: 5.1, 5.6_

- [ ] 25. Create authorization policies
  - Create `app/Policies/SiswaPolicy.php` with CRUD methods
  - Create `app/Policies/RiwayatPelanggaranPolicy.php`
  - Create `app/Policies/TindakLanjutPolicy.php`
  - Register policies in `AuthServiceProvider`
  - Use policies in Form Request authorize() methods
  - _Requirements: 16.1, 16.2, 16.3, 16.5_

- [ ]* 25.1 Write property test for policy authorization
  - **Property 29: Policy Authorization Checks**
  - **Validates: Requirements 16.2, 16.4**

---

## Phase 15: Caching Implementation

- [ ] 26. Implement caching in repositories
  - Add caching to `JenisPelanggaranRepository` for static data
  - Add caching to `KelasRepository` and `JurusanRepository`
  - Use cache tags for grouped invalidation
  - Set appropriate TTL for each cache type
  - _Requirements: 11.1, 11.3, 11.5_

- [ ]* 26.1 Write property test for cache usage
  - **Property 19: Cache Usage for Static Data**
  - **Validates: Requirements 11.1**

- [ ] 27. Implement cache invalidation in services
  - Invalidate siswa cache when siswa is created/updated/deleted
  - Invalidate pelanggaran cache when riwayat is created/updated
  - Use cache tags for efficient invalidation
  - _Requirements: 11.2_

- [ ]* 27.1 Write property test for cache invalidation
  - **Property 20: Cache Invalidation on Data Change**
  - **Validates: Requirements 11.2**

---

## Phase 16: Exception Handling

- [ ] 28. Create custom exception hierarchy
  - Create `app/Exceptions/DomainException.php` (abstract base)
  - Create `app/Exceptions/SiswaNotFoundException.php`
  - Create `app/Exceptions/UnauthorizedException.php`
  - Create `app/Exceptions/ValidationException.php`
  - Implement getUserMessage() and getLogContext() methods
  - _Requirements: 17.2_

- [ ] 29. Configure global exception handler
  - Update `app/Exceptions/Handler.php` to handle DomainExceptions
  - Log exceptions with context (user, request, stack trace)
  - Return user-friendly messages to users
  - Return JSON for API requests
  - _Requirements: 17.1, 17.4_

- [ ]* 29.1 Write property test for exception logging
  - **Property 30: Exception Logging Context**
  - **Validates: Requirements 17.1**

- [ ] 30. Configure logging channels
  - Configure `audit` channel for user actions
  - Configure `performance` channel for slow queries
  - Configure `slack` channel for critical errors
  - _Requirements: 17.3, 17.5_

- [ ]* 30.1 Write property test for log channel routing
  - **Property 31: Log Channel Routing**
  - **Validates: Requirements 17.3**

---

## Phase 17: Queue Jobs

- [ ] 31. Create queue jobs for async operations
  - Create `app/Jobs/SendNotificationEmail.php` for email notifications
  - Create `app/Jobs/ProcessBulkSiswaImport.php` for bulk operations
  - Implement error handling and retry logic
  - Configure queue workers in `config/queue.php`
  - _Requirements: 18.1, 18.2, 18.3, 18.4_

- [ ]* 31.1 Write property test for email queueing
  - **Property 33: Email Queueing**
  - **Validates: Requirements 18.1**

- [ ]* 31.2 Write property test for job retry logic
  - **Property 36: Job Retry Logic**
  - **Validates: Requirements 18.4**

---

## Phase 18: Route Organization

- [ ] 32. Organize routes into domain-specific files
  - Create `routes/siswa.php` for Siswa routes
  - Create `routes/pelanggaran.php` for Pelanggaran routes
  - Create `routes/tindak-lanjut.php` for Tindak Lanjut routes
  - Create `routes/user.php` for User management routes
  - Use route groups with middleware and prefixes
  - Use consistent naming conventions
  - _Requirements: 7.1, 7.2, 7.3, 7.4_

- [ ] 33. Register route files in bootstrap/app.php
  - Register all domain route files
  - Apply appropriate middleware to each group
  - _Requirements: 7.5_

- [ ]* 33.1 Write property test for route middleware
  - **Property 10: Route Middleware Application**
  - **Validates: Requirements 7.2**

---

## Phase 19: Database Seeders and Factories

- [ ] 34. Create model factories with realistic data
  - Update `SiswaFactory` to use enum for status
  - Update `JenisPelanggaranFactory` to use enum for tingkat
  - Create `RiwayatPelanggaranFactory` with proper relationships
  - Use realistic fake data
  - _Requirements: 15.1, 15.3_

- [ ]* 34.1 Write property test for factory data validity
  - **Property 27: Factory Data Validity**
  - **Validates: Requirements 15.3**

- [ ] 35. Create domain-specific seeders
  - Create `JurusanSeeder`, `KelasSeeder`
  - Create `JenisPelanggaranSeeder` with enum values
  - Create `UserSeeder` for test users
  - Ensure idempotent seeding
  - _Requirements: 15.2, 15.4, 15.5_

- [ ]* 35.1 Write property test for seeder idempotency
  - **Property 28: Seeder Idempotency**
  - **Validates: Requirements 15.4**

---

## Phase 20: Blade Components (Optional)

- [ ] 36. Create reusable Blade components
  - Create `resources/views/components/siswa/status-badge.blade.php` using enum
  - Create `resources/views/components/pelanggaran/tingkat-badge.blade.php`
  - Create `resources/views/components/table/pagination.blade.php`
  - Use typed component properties
  - Organize by feature domain
  - _Requirements: 19.1, 19.3, 19.5_

- [ ]* 36.1 Write property test for component type safety
  - **Property 37: Blade Component Type Safety**
  - **Validates: Requirements 19.3**

---

## Phase 21: Code Quality and Standards

- [ ] 37. Configure and run Laravel Pint
  - Create `pint.json` with PSR-12 configuration
  - Run Pint on entire codebase
  - Fix any formatting issues
  - _Requirements: 13.1, 13.2, 13.5_

- [ ] 38. Review naming conventions
  - Verify all class names use PascalCase
  - Verify all method names use camelCase
  - Verify all variables use descriptive names
  - Verify database columns use snake_case
  - _Requirements: 14.1, 14.2, 14.3, 14.4_

---

## Phase 22: Final Testing and Documentation

- [ ] 39. Run complete test suite
  - Run all unit tests
  - Run all property tests (minimum 100 iterations each)
  - Verify all tests pass
  - Check test coverage

- [ ] 40. Performance benchmarking
  - Measure query counts for list endpoints
  - Measure response times
  - Verify cache hit rates
  - Ensure no N+1 queries

- [ ] 41. Update project documentation
  - Document folder structure in README
  - Document DTO usage patterns
  - Document enum usage
  - Document service layer patterns
  - _Requirements: 20.5_

- [ ] 42. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise

---

## Summary

This implementation plan covers:
- ✅ 21 Requirements with 83+ acceptance criteria
- ✅ 40+ Correctness Properties for property-based testing
- ✅ Strict HTTP decoupling (Services never receive Request objects)
- ✅ DTO-based data flow throughout all layers
- ✅ PHP Native Enums for type safety
- ✅ Repository pattern with interfaces
- ✅ Comprehensive testing strategy (unit + property tests)
- ✅ Performance optimization (indexing, caching, eager loading)
- ✅ Clean architecture principles

**Total Tasks**: 42 main tasks with 20+ optional test sub-tasks
**Estimated Timeline**: 10-13 weeks for complete implementation

**Key Changes from Original Plan:**
- Phase 4 now includes User repository foundation (Task 7) to resolve dependency issue
- UserNamingService moved to Phase 5 (Task 10) before SiswaService
- Phase 14 focuses on completing User service, not creating it from scratch
- All subsequent task numbers adjusted accordingly
