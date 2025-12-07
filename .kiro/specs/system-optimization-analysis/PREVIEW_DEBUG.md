# Preview Feature - Debug & Fix

## Issue Reported
**Error**: "Gagal memuat preview. Silakan coba lagi."

## Root Cause Analysis

### Issue 1: Missing CSRF Token Meta Tag ✅ FIXED
**Problem**: Layout tidak memiliki `<meta name="csrf-token">` tag  
**Impact**: AJAX request gagal karena CSRF validation  
**Solution**: Added to `resources/views/layouts/app.blade.php`

```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

### Issue 2: Route Cache
**Problem**: Route mungkin ter-cache  
**Solution**: Cleared route cache
```bash
php artisan route:clear
```

### Issue 3: Error Handling
**Problem**: Error messages tidak informatif  
**Solution**: 
- Added detailed console logging in JavaScript
- Added try-catch in controller with proper error responses
- Added Laravel logging for server-side errors

## Files Modified

### 1. `resources/views/layouts/app.blade.php` ✅
Added CSRF token meta tag in `<head>` section

### 2. `public/js/pages/pelanggaran/create.js` ✅
Enhanced error handling:
- Added `console.log` for response status and data
- Better error messages with details
- Prompt user to check browser console

### 3. `app/Http/Controllers/Pelanggaran/PelanggaranController.php` ✅
Enhanced preview method:
- Wrapped in try-catch
- Separate handling for validation errors (422)
- Separate handling for server errors (500)
- Laravel logging for debugging

## Testing Steps

### 1. Clear Browser Cache
```
Ctrl + Shift + Delete (Chrome/Edge)
Cmd + Shift + Delete (Mac)
```

### 2. Hard Refresh Page
```
Ctrl + F5 (Windows)
Cmd + Shift + R (Mac)
```

### 3. Test Preview
1. Login as Guru/Wali Kelas
2. Go to "Catat Pelanggaran"
3. Select at least 1 student
4. Select at least 1 violation
5. Click "PREVIEW DAMPAK"
6. Open Browser Console (F12)
7. Check console output

### Expected Console Output:
```
Response status: 200
Response data: {success: true, html: "...", requires_confirmation: false}
```

### If Error Occurs:
Check console for:
- Network errors (CORS, 404, 500)
- Response errors (validation, server error)
- JavaScript errors

## Debugging Commands

### Check Route Exists:
```bash
php artisan route:list | findstr preview
```

Expected output:
```
POST  pelanggaran/preview  pelanggaran.preview › Pelanggaran\PelanggaranController@preview
```

### Check Laravel Logs:
```bash
# Windows
type storage\logs\laravel.log | findstr "Preview error"

# Linux/Mac
tail -f storage/logs/laravel.log | grep "Preview error"
```

### Test AJAX Manually (Browser Console):
```javascript
// Test if CSRF token exists
console.log(document.querySelector('meta[name="csrf-token"]')?.content);

// Test AJAX call
fetch('/pelanggaran/preview', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'X-Requested-With': 'XMLHttpRequest'
    },
    body: JSON.stringify({
        siswa_id: [1],
        jenis_pelanggaran_id: [1]
    })
})
.then(r => r.json())
.then(d => console.log(d));
```

## Common Issues & Solutions

### Issue: 419 CSRF Token Mismatch
**Solution**: 
- Clear browser cache
- Hard refresh (Ctrl+F5)
- Check CSRF token meta tag exists

### Issue: 404 Not Found
**Solution**:
```bash
php artisan route:clear
php artisan config:clear
```

### Issue: 500 Internal Server Error
**Solution**:
- Check `storage/logs/laravel.log`
- Verify `PelanggaranPreviewService` exists
- Check database connection

### Issue: 422 Validation Error
**Solution**:
- Ensure at least 1 student selected
- Ensure at least 1 violation selected
- Check siswa_id and jenis_pelanggaran_id exist in database

### Issue: Modal Not Showing
**Solution**:
- Check jQuery is loaded
- Check Bootstrap JS is loaded
- Check modal HTML exists in DOM

## Verification Checklist

After fix, verify:
- [ ] CSRF token meta tag exists in page source
- [ ] Route `pelanggaran/preview` exists in route list
- [ ] Browser console shows no errors
- [ ] Preview button shows loading state
- [ ] Modal opens with content
- [ ] "Lanjutkan Pencatatan" button works

## Status

**Root Cause**: Missing CSRF token meta tag  
**Fix Applied**: ✅ YES  
**Testing**: ⏳ PENDING USER VERIFICATION  
**Expected Result**: Preview should work now

---

## Next Steps

1. **Clear browser cache and hard refresh**
2. **Test preview again**
3. **If still error, check browser console (F12)**
4. **Share console error message for further debugging**
