# Quick Reference: Enum Usage in Blade Views

## 📋 Overview

PHP 8.1+ backed enums **CANNOT** have `__toString()` magic method. Use `->value` property instead.

---

## ✅ CORRECT Usage

### 1. Comparing Enum Values

```blade
<!-- ✅ CORRECT: Compare with ->value -->
@if($tindakLanjut->status->value == 'Baru')
    <span class="badge badge-primary">New Case</span>
@endif

@if($tindakLanjut->status->value == 'Menunggu Persetujuan')
    <button>Approve</button>
@endif
```

### 2. Displaying Enum Values

```blade
<!-- ✅ CORRECT: Echo ->value -->
<td>{{ $tindakLanjut->status->value }}</td>

<!-- ✅ BETTER: Use ->label() for formatted display -->
<td>{{ $tindakLanjut->status->label() }}</td>
```

### 3. Badge with Color

```blade
<!-- ✅ CORRECT: Use helper methods -->
<span class="badge badge-{{ $tindakLanjut->status->color() }}">
    {{ $tindakLanjut->status->label() }}
</span>
```

### 4. Switch/Case Statements

```blade
<!-- ✅ CORRECT: Switch on ->value -->
@switch($tindakLanjut->status->value)
    @case('Baru')
        <span class="text-primary">New</span>
        @break
    @case('Menunggu Persetujuan')
        <span class="text-warning">Pending</span>
        @break
    @case('Disetujui')
        <span class="text-success">Approved</span>
        @break
@endswitch
```

### 5. Using Helper Methods

```blade
<!-- ✅ CORRECT: Check status with helper methods -->
@if($tindakLanjut->status->isPendingApproval())
    <button>Review Now</button>
@endif

@if($tindakLanjut->status->isActive())
    <span class="badge badge-success">Active</span>
@endif
```

---

## ❌ WRONG Usage

### Common Mistakes

```blade
<!-- ❌ WRONG: Direct comparison (won't work!) -->
@if($status == 'Baru')
    <!-- This will NEVER be true! -->
@endif

<!-- ❌ WRONG: Direct echo (outputs enum case name, not value) -->
<td>{{ $status }}</td>
<!-- Outputs: "BARU" instead of "Baru" -->

<!-- ❌ WRONG: Using __toString() (doesn't exist!) -->
<td>{{ $status->__toString() }}</td>
<!-- Fatal error! -->
```

---

## 📚 Available Enum Methods

### StatusTindakLanjut

```php
$status->value          // String: 'Baru', 'Menunggu Persetujuan', etc.
$status->label()        // String: Same as value
$status->color()        // String: 'info', 'warning', 'success', 'danger', etc.

// Boolean helpers
$status->isActive()              // true if not completed/rejected
$status->isPendingApproval()     // true if 'Menunggu Persetujuan'
$status->isCompleted()           // true if 'Selesai'
$status->isRejected()            // true if 'Ditolak'
```

**Usage in views:**
```blade
@if($item->status->isPendingApproval())
    <button class="btn btn-warning">Approve</button>
@endif

<span class="badge badge-{{ $item->status->color() }}">
    {{ $item->status->label() }}
</span>
```

### TingkatPelanggaran

```php
$tingkat->value         // String: 'RINGAN', 'SEDANG', 'BERAT'
$tingkat->label()       // String: 'Ringan', 'Sedang', 'Berat' (capitalized)
$tingkat->color()       // String: 'success', 'warning', 'danger'
$tingkat->poinRange()   // Array: [min, max] points
```

**Usage in views:**
```blade
<span class="badge badge-{{ $pelanggaran->tingkat->color() }}">
    {{ $pelanggaran->tingkat->label() }}
</span>

<small class="text-muted">
    Poin: {{ $pelanggaran->tingkat->poinRange()[0] }}-{{ $pelanggaran->tingkat->poinRange()[1] }}
</small>
```

---

## 🎯 Best Practices

### 1. Always Use ->value for String Operations

```blade
<!-- Comparison -->
@if($status->value == 'Baru')

<!-- String concatenation -->
{{ 'Status: ' . $status->value }}

<!-- In URLs -->
<a href="/filter?status={{ $status->value }}">
```

### 2. Use Helper Methods When Available

```blade
<!-- Instead of: -->
@if($status->value == 'Menunggu Persetujuan')

<!-- Use: -->
@if($status->isPendingApproval())
```

### 3. Use ->label() for Display

```blade
<!-- Instead of: -->
<td>{{ $status->value }}</td>

<!-- Use (if different formatting): -->
<td>{{ $status->label() }}</td>
```

### 4. Use ->color() for Badges

```blade
<!-- Consistent color coding -->
<span class="badge badge-{{ $status->color() }}">
    {{ $status->label() }}
</span>
```

---

## 🔧 Migration Guide

### If You Have Existing Views

**Find and replace:**

```bash
# Find all direct enum comparisons
grep -r "== 'Baru'" resources/views/

# Replace with:
# Before: @if($status == 'Baru')
# After:  @if($status->value == 'Baru')
```

**Pattern:**
```blade
<!-- BEFORE -->
@if($item->status == 'Baru')
{{ $item->status }}

<!-- AFTER -->
@if($item->status->value == 'Baru')
{{ $item->status->value }}

<!-- OR BETTER -->
@if($item->status->isPendingApproval())
{{ $item->status->label() }}
```

---

## 📝 Quick Reference Table

| Operation | ❌ Wrong | ✅ Correct |
|-----------|---------|-----------|
| Compare | `$status == 'Baru'` | `$status->value == 'Baru'` |
| Echo | `{{ $status }}` | `{{ $status->value }}` |
| Label | `{{ $status }}` | `{{ $status->label() }}` |
| Check | `$status == 'Baru'` | `$status->isPendingApproval()` |
| Badge | `badge-primary` | `badge-{{ $status->color() }}` |

---

## 💡 Pro Tips

1. **Use helper methods** untuk better readability
2. **Use ->label()** untuk formatted display
3. **Use ->color()** untuk consistent styling
4. **Always ->value** untuk string operations
5. **Never** assume enum can be used as string directly

---

**Created:** 2025-12-08  
**For:** SMKN 1 Kedisiplinan Project  
**PHP Version:** 8.2+  
**Laravel Version:** 12.x
