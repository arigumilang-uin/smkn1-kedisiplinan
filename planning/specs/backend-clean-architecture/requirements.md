# Requirements Document

## Introduction

Sistem ini adalah aplikasi manajemen kedisiplinan siswa SMKN1 yang dibangun dengan Laravel. Saat ini, sistem memiliki beberapa masalah arsitektur yang perlu diperbaiki untuk meningkatkan maintainability, testability, dan performance. Refactoring ini akan menerapkan clean architecture principles dan Laravel best practices untuk menghasilkan codebase yang lebih terstruktur, mudah dipelihara, dan performant.

## Glossary

- **Backend System**: Aplikasi Laravel yang menangani business logic, data access, dan API endpoints
- **Migration**: File database schema yang mendeskripsikan struktur tabel dan perubahan database
- **Model**: Eloquent model yang merepresentasikan entitas database dan relasi antar tabel
- **DTO (Data Transfer Object)**: Object yang membawa data antar layer aplikasi dengan struktur yang jelas dan type-safe
- **Repository**: Layer yang bertanggung jawab untuk data access logic dan query operations
- **Service**: Layer yang mengandung business logic, validasi kompleks, dan orchestration
- **Controller**: Layer yang menangani HTTP request/response dan routing logic
- **Form Request**: Class validasi Laravel yang menangani input validation dan authorization
- **Eager Loading**: Teknik loading relasi database secara efisien untuk menghindari N+1 query problem
- **Query Scope**: Method di model untuk encapsulate query logic yang reusable
- **Index**: Database index untuk mempercepat query performance
- **Caching**: Mekanisme penyimpanan temporary data untuk mengurangi database load

## Requirements

### Requirement 1: Database Migration Structure

**User Story:** As a developer, I want well-organized database migrations, so that schema changes are clear, traceable, and maintainable.

#### Acceptance Criteria

1. WHEN creating new migrations THEN the Backend System SHALL use descriptive names that clearly indicate the purpose and affected tables
2. WHEN modifying database schema THEN the Backend System SHALL separate each logical change into individual migration files
3. WHEN creating tables THEN the Backend System SHALL include appropriate indexes, foreign keys, and constraints in the initial migration
4. WHEN adding columns to existing tables THEN the Backend System SHALL create separate migration files for each distinct feature or change
5. WHERE migrations affect data integrity THEN the Backend System SHALL include both up and down methods with proper rollback logic

### Requirement 2: Model Organization

**User Story:** As a developer, I want clean and focused models, so that business logic is properly separated and models remain maintainable.

#### Acceptance Criteria

1. WHEN defining models THEN the Backend System SHALL limit model responsibilities to relationships, attribute casting, and lightweight query scopes
2. WHEN defining relationships THEN the Backend System SHALL use proper Eloquent relationship methods with explicit foreign keys and return types
3. WHEN casting attributes THEN the Backend System SHALL define all casts in the $casts property for type safety
4. WHEN creating query scopes THEN the Backend System SHALL keep scopes simple and focused on single filtering concerns
5. THE Backend System SHALL NOT place complex business logic, validation rules, or data manipulation in model classes

### Requirement 3: Data Transfer Objects (DTOs)

**User Story:** As a developer, I want type-safe data structures, so that data flow between layers is predictable and testable.

#### Acceptance Criteria

1. WHEN transferring data between layers THEN the Backend System SHALL use DTO classes instead of raw arrays
2. WHEN creating DTOs THEN the Backend System SHALL define all properties with explicit types and validation rules
3. WHEN validating input data THEN the Backend System SHALL integrate DTOs with Form Request validation
4. WHEN transforming data THEN the Backend System SHALL use DTOs to ensure consistent data structure across the application
5. THE Backend System SHALL use Laravel Data package or similar for DTO implementation with validation integration

### Requirement 4: Repository Pattern

**User Story:** As a developer, I want centralized data access logic, so that queries are reusable and controllers remain thin.

#### Acceptance Criteria

1. WHEN accessing database THEN the Backend System SHALL use repository classes for all data access operations
2. WHEN creating repositories THEN the Backend System SHALL define interface contracts for dependency injection
3. WHEN building queries THEN the Backend System SHALL encapsulate filtering, sorting, and pagination logic in repository methods
4. WHEN performing complex queries THEN the Backend System SHALL keep query logic in repositories rather than controllers or services
5. THE Backend System SHALL bind repository interfaces to implementations in service providers

### Requirement 5: Service Layer

**User Story:** As a developer, I want business logic separated from controllers, so that logic is reusable and testable.

#### Acceptance Criteria

1. WHEN implementing business rules THEN the Backend System SHALL place logic in service classes
2. WHEN orchestrating multiple operations THEN the Backend System SHALL use services to coordinate between repositories
3. WHEN performing complex validations THEN the Backend System SHALL implement validation logic in service layer
4. WHEN integrating internal systems THEN the Backend System SHALL use services to handle cross-domain operations
5. THE Backend System SHALL inject repositories and other dependencies into service constructors
6. THE Backend System SHALL NOT inject or use Illuminate\Http\Request or Form Request classes inside Service methods; strictly use DTOs or primitive types.

### Requirement 6: Controller Optimization

**User Story:** As a developer, I want thin controllers, so that HTTP layer remains simple and focused.

#### Acceptance Criteria

1. WHEN handling requests THEN the Backend System SHALL limit controller responsibilities to validation, service calls, and response formatting
2. WHEN validating input THEN the Backend System SHALL use Form Request classes instead of inline validation
3. WHEN processing business logic THEN the Backend System SHALL delegate to service layer
4. WHEN returning responses THEN the Backend System SHALL use consistent response format with proper HTTP status codes
5. THE Backend System SHALL keep controller methods under 20 lines of code where possible

### Requirement 7: Route Organization

**User Story:** As a developer, I want organized route files, so that endpoints are easy to find and maintain.

#### Acceptance Criteria

1. WHEN defining routes THEN the Backend System SHALL separate routes into multiple files by domain or module
2. WHEN grouping routes THEN the Backend System SHALL use route groups with appropriate middleware and prefixes
3. WHEN naming routes THEN the Backend System SHALL use consistent naming conventions that reflect resource hierarchy
4. WHEN organizing route files THEN the Backend System SHALL create separate files for each major feature domain
5. THE Backend System SHALL register route files in RouteServiceProvider or bootstrap/app.php

### Requirement 8: Form Request Validation

**User Story:** As a developer, I want centralized validation logic, so that validation rules are reusable and consistent.

#### Acceptance Criteria

1. WHEN validating input THEN the Backend System SHALL create dedicated Form Request classes for each operation
2. WHEN defining validation rules THEN the Backend System SHALL use Laravel validation rules with custom rules where needed
3. WHEN authorizing requests THEN the Backend System SHALL implement authorization logic in Form Request authorize method
4. WHEN customizing error messages THEN the Backend System SHALL define messages in Form Request classes
5. THE Backend System SHALL use Form Requests for all controller actions that accept user input

### Requirement 9: Query Performance Optimization

**User Story:** As a developer, I want optimized database queries, so that application performance is fast and scalable.

#### Acceptance Criteria

1. WHEN loading related data THEN the Backend System SHALL use eager loading to prevent N+1 query problems
2. WHEN selecting data THEN the Backend System SHALL specify only required columns instead of using SELECT *
3. WHEN implementing pagination THEN the Backend System SHALL paginate all list endpoints with reasonable page sizes
4. WHEN executing queries in loops THEN the Backend System SHALL refactor to use batch operations or eager loading
5. THE Backend System SHALL NOT execute queries inside loops or blade templates

### Requirement 10: Database Indexing

**User Story:** As a developer, I want proper database indexes, so that query performance is optimized.

#### Acceptance Criteria

1. WHEN creating foreign key columns THEN the Backend System SHALL add indexes on foreign key columns
2. WHEN filtering by columns THEN the Backend System SHALL add indexes on frequently queried columns
3. WHEN sorting results THEN the Backend System SHALL add indexes on columns used in ORDER BY clauses
4. WHEN searching data THEN the Backend System SHALL add composite indexes for multi-column searches
5. THE Backend System SHALL create migration files to add missing indexes to existing tables

### Requirement 11: Caching Strategy

**User Story:** As a developer, I want effective caching, so that database load is reduced and response times are faster.

#### Acceptance Criteria

1. WHEN querying static data THEN the Backend System SHALL cache results with appropriate TTL
2. WHEN data changes THEN the Backend System SHALL invalidate related cache entries
3. WHEN implementing caching THEN the Backend System SHALL use Laravel cache facade with proper cache keys
4. WHEN caching queries THEN the Backend System SHALL cache at repository or service layer
5. THE Backend System SHALL use cache tags for grouped cache invalidation where supported

### Requirement 12: File Upload Optimization

**User Story:** As a developer, I want optimized file handling, so that file uploads are secure and performant.

#### Acceptance Criteria

1. WHEN storing uploaded files THEN the Backend System SHALL use Laravel storage system with proper disk configuration
2. WHEN serving files THEN the Backend System SHALL use storage:link for public file access
3. WHEN validating uploads THEN the Backend System SHALL validate file types, sizes, and mime types
4. WHEN deleting records THEN the Backend System SHALL clean up associated files from storage
5. THE Backend System SHALL store files in organized directory structures by type and date

### Requirement 13: PSR-12 Coding Standards

**User Story:** As a developer, I want consistent code formatting, so that codebase is readable and maintainable.

#### Acceptance Criteria

1. WHEN writing PHP code THEN the Backend System SHALL follow PSR-12 coding standards
2. WHEN formatting code THEN the Backend System SHALL use Laravel Pint for automatic code formatting
3. WHEN naming classes THEN the Backend System SHALL use PascalCase for class names
4. WHEN naming methods THEN the Backend System SHALL use camelCase for method names
5. THE Backend System SHALL configure Pint rules in pint.json for project-specific standards

### Requirement 14: Meaningful Naming Conventions

**User Story:** As a developer, I want clear and consistent naming, so that code intent is immediately understandable.

#### Acceptance Criteria

1. WHEN naming variables THEN the Backend System SHALL use descriptive names that reveal intent
2. WHEN naming methods THEN the Backend System SHALL use verb-noun combinations that describe actions
3. WHEN naming classes THEN the Backend System SHALL use nouns that represent domain concepts
4. WHEN naming database columns THEN the Backend System SHALL use snake_case with clear descriptive names
5. THE Backend System SHALL avoid abbreviations unless they are widely understood domain terms

### Requirement 15: Database Seeders and Factories

**User Story:** As a developer, I want reliable test data generation, so that development and testing environments are consistent.

#### Acceptance Criteria

1. WHEN creating test data THEN the Backend System SHALL use factory classes for model generation
2. WHEN seeding database THEN the Backend System SHALL create seeder classes for each domain entity
3. WHEN defining factories THEN the Backend System SHALL use realistic fake data with proper relationships
4. WHEN running seeders THEN the Backend System SHALL ensure idempotent seeding that can run multiple times
5. THE Backend System SHALL organize seeders by domain and execution order

### Requirement 16: Authorization Policies

**User Story:** As a developer, I want centralized authorization logic, so that access control is consistent and maintainable.

#### Acceptance Criteria

1. WHEN authorizing actions THEN the Backend System SHALL use Laravel Policy classes for resource authorization
2. WHEN checking permissions THEN the Backend System SHALL use policy methods in controllers and Form Requests
3. WHEN defining policies THEN the Backend System SHALL create one policy per model with standard CRUD methods
4. WHEN implementing role-based access THEN the Backend System SHALL use policies combined with role checks
5. THE Backend System SHALL register policies in AuthServiceProvider

### Requirement 17: Exception Handling and Logging

**User Story:** As a developer, I want comprehensive error handling, so that issues are traceable and debuggable.

#### Acceptance Criteria

1. WHEN exceptions occur THEN the Backend System SHALL log errors with appropriate context and severity levels
2. WHEN handling errors THEN the Backend System SHALL use custom exception classes for domain-specific errors
3. WHEN logging events THEN the Backend System SHALL use Laravel logging channels for different log types
4. WHEN catching exceptions THEN the Backend System SHALL provide user-friendly error messages while logging technical details
5. THE Backend System SHALL configure log channels in logging.php for different environments

### Requirement 18: Queue System for Heavy Tasks

**User Story:** As a developer, I want asynchronous processing, so that heavy operations do not block user requests.

#### Acceptance Criteria

1. WHEN sending emails THEN the Backend System SHALL queue email jobs for asynchronous processing
2. WHEN processing large files THEN the Backend System SHALL use queue jobs for file processing operations
3. WHEN performing bulk operations THEN the Backend System SHALL dispatch jobs to queue system
4. WHEN creating jobs THEN the Backend System SHALL implement proper error handling and retry logic
5. THE Backend System SHALL configure queue workers and failed job handling

### Requirement 19: Blade Component Organization

**User Story:** As a developer, I want reusable UI components, so that views follow DRY principles.

#### Acceptance Criteria

1. WHEN creating views THEN the Backend System SHALL use Blade components for reusable UI elements
2. WHEN organizing layouts THEN the Backend System SHALL use component-based layouts instead of @extends
3. WHEN passing data to components THEN the Backend System SHALL use typed component properties
4. WHEN styling components THEN the Backend System SHALL keep component logic separate from styling
5. THE Backend System SHALL organize components in resources/views/components by feature domain

### Requirement 20: Folder Structure Refinement

**User Story:** As a developer, I want logical folder organization, so that related code is grouped together.

#### Acceptance Criteria

1. WHEN organizing application code THEN the Backend System SHALL group related classes by domain or feature
2. WHEN creating new features THEN the Backend System SHALL follow consistent folder structure patterns
3. WHEN separating concerns THEN the Backend System SHALL use dedicated folders for DTOs, Repositories, Services, and Policies
4. WHEN organizing tests THEN the Backend System SHALL mirror application structure in test folders
5. THE Backend System SHALL document folder structure conventions in project documentation

### Requirement 21: Native Enums Utilization

**User Story:** As a developer, I want to use PHP Native Enums for fixed status values, so that data consistency and type safety are guaranteed.

### Acceptance Criteria

1. THE Backend System SHALL use PHP 8.1+ Native Enums for all status columns (e.g., StatusSiswa, TingkatPelanggaran).
2. WHEN defining models THEN the Backend System SHALL cast status attributes to their respective Enum classes.
3. THE Backend System SHALL NOT use hardcoded strings or magic numbers for status comparisons in business logic.
