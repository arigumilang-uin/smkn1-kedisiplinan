# PSR-12 Code Quality Checklist

## Automated Check (Run this first)
```bash
./vendor/bin/pint --test
./vendor/bin/pint  # Auto fix issues
```

## Manual Verification Checklist

### ✅ Naming Conventions

- [ ] **Classes**: PascalCase (e.g., `SiswaRepository`, `UserService`)
- [ ] **Methods**: camelCase (e.g., `createSiswa()`, `findByNisn()`)
- [ ] **Variables**: camelCase (e.g., `$siswaData`, `$totalPoin`)
- [ ] **Constants**: UPPER_SNAKE_CASE (e.g., `SURAT_1`, `MAX_RETRIES`)
- [ ] **Database columns**: snake_case (e.g., `siswa_id`, `tanggal_kejadian`)

### ✅ Type Declarations

- [ ] All method parameters have type hints
- [ ] All method return types are declared
- [ ] Property types declared (PHP 7.4+)
- [ ] No mixed types unless necessary

Example:
```php
// ✅ Good
public function createSiswa(SiswaData $data): SiswaData

// ❌ Bad
public function createSiswa($data)
```

### ✅ Imports & Namespaces

- [ ] All classes properly namespaced
- [ ] Unused imports removed
- [ ] Imports alphabetically sorted (PSR-12)
- [ ] One blank line after namespace declaration
- [ ] One blank line after use statements

### ✅ Visibility Keywords

- [ ] All properties have visibility (public/private/protected)
- [ ] All methods have visibility
- [ ] Constructor properties use promoted syntax (PHP 8.0+)

Example:
```php
// ✅ Good
public function __construct(
    private SiswaRepositoryInterface $siswaRepo
) {}

// ❌ Bad
function __construct($siswaRepo) {}
```

### ✅ Indentation & Formatting

- [ ] 4 spaces for indentation (NO TABS)
- [ ] Opening braces on same line for methods
- [ ] One statement per line
- [ ] Max 120 characters per line (recommend 80-100)
- [ ] Trailing commas in multi-line arrays

### ✅ Documentation

- [ ] All classes have docblocks
- [ ] All public methods have docblocks
- [ ] @param tags match parameter order
- [ ] @return tags accurate
- [ ] @throws documented for exceptions

Example:
```php
/**
 * Create siswa baru.
 *
 * @param SiswaData $data
 * @return SiswaData
 * @throws \Exception
 */
public function createSiswa(SiswaData $data): SiswaData
```

### ✅ Error Handling

- [ ] No suppressed errors (@)
- [ ] Proper exception types thrown
- [ ] Try-catch only where needed
- [ ] Finally blocks for cleanup

### ✅ Best Practices

- [ ] No `die()` or `dd()` in production code
- [ ] No `var_dump()` or `print_r()` 
- [ ] SQL injection prevention (use parameterized queries)
- [ ] XSS prevention (use Blade `{{ }}` not `{!! !!}`)
- [ ] CSRF protection on forms

### ✅ Request/Response

- [ ] Controllers don't accept Request in services
- [ ] DTOs used for data transfer
- [ ] FormRequests used for validation
- [ ] Proper HTTP status codes

### ✅ Database

- [ ] Use query builder or Eloquent (no raw SQL unless necessary)
- [ ] Eager loading to prevent N+1
- [ ] Indexes on foreign keys
- [ ] Transactions for multi-step operations

### ✅ Security

- [ ] Passwords hashed with bcrypt/argon2
- [ ] Authorization checks (Policies)
- [ ] Input validation (FormRequests)
- [ ] Mass assignment protection ($fillable)

### ✅ Performance

- [ ] Caching for read-heavy data
- [ ] Queue jobs for slow operations
- [ ] Pagination for large datasets
- [ ] Select only needed columns

## Common Issues to Fix

### Issue 1: Missing Return Types
```php
// ❌ Bad
public function find($id)
{
    return $this->model->find($id);
}

// ✅ Good
public function find(int $id): ?Model
{
    return $this->model->find($id);
}
```

### Issue 2: Missing Type Hints
```php
// ❌ Bad
public function create($data)

// ✅ Good
public function create(array $data): Model
```

### Issue 3: No Visibility
```php
// ❌ Bad
function handle()

// ✅ Good
public function handle(): void
```

### Issue 4: Unused Imports
```php
// ❌ Bad
use App\Models\User;  // Not used in file

// ✅ Good - Remove unused imports
```

### Issue 5: Long Lines
```php
// ❌ Bad (150+ characters)
public function someVeryLongMethodNameWithManyParameters($param1, $param2, $param3, $param4, $param5, $param6)

// ✅ Good (multi-line)
public function someVeryLongMethodNameWithManyParameters(
    string $param1,
    string $param2,
    string $param3
): void
```

## IDE Configuration

### VS Code Settings
```json
{
    "editor.tabSize": 4,
    "editor.insertSpaces": true,
    "editor.rulers": [80, 120],
    "php.suggest.basic": true,
    "php.validate.enable": true
}
```

### PHPStorm
- Settings > Editor > Code Style > PHP
- Set from: PSR-12
- Tabs and Indents: Use 4 spaces

## Laravel Pint Configuration

Create `pint.json`:
```json
{
    "preset": "psr12",
    "rules": {
        "simplified_null_return": true,
        "no_unused_imports": true,
        "ordered_imports": true
    }
}
```

## Review Checklist Summary

- [ ] Run `./vendor/bin/pint --test`
- [ ] Check all naming conventions
- [ ] Verify type declarations
- [ ] Review docblocks
- [ ] Test error handling
- [ ] Validate security measures
- [ ] Check performance optimizations
- [ ] Remove debug code (dd, dump, etc.)

**Target: 0 Pint errors, 0 PHPStan errors (Level 8)**
