# 🎨 FRONTEND INTEGRATION GUIDE - Preview Before Submit

**Feature**: Preview Before Submit  
**Status**: Backend ✅ READY | Frontend ⏳ PENDING  
**Estimasi**: 1 jam

---

## 📋 OVERVIEW

Backend sudah siap dengan:
- ✅ AJAX endpoint: `POST /pelanggaran/preview`
- ✅ Preview service logic
- ✅ Modal partial view
- ✅ Route registered

Yang perlu ditambahkan di frontend:
- 🔧 Preview button di form
- 🔧 AJAX call ke endpoint
- 🔧 Modal display
- 🔧 Submit after confirmation

---

## 🔧 IMPLEMENTATION STEPS

### **Step 1: Tambahkan Preview Button**

**File**: `resources/views/pelanggaran/create.blade.php`

**Lokasi**: Sebelum button "SIMPAN DATA" (line ~245)

```html
<!-- Preview & Submit Buttons -->
<div class="row">
    <div class="col-md-6">
        <button type="button" id="btnPreview" class="btn btn-info btn-block font-weight-bold shadow-sm">
            <i class="fas fa-eye mr-1"></i> PREVIEW DAMPAK
        </button>
    </div>
    <div class="col-md-6">
        <button type="submit" id="btnSubmit" class="btn btn-primary btn-block font-weight-bold shadow-sm">
            <i class="fas fa-save mr-1"></i> SIMPAN DATA
        </button>
    </div>
</div>
```

---

### **Step 2: Tambahkan Preview Modal Container**

**File**: `resources/views/pelanggaran/create.blade.php`

**Lokasi**: Sebelum `@endpush` (akhir file)

```html
<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white">
                    <i class="fas fa-eye mr-2"></i> Preview Dampak Pencatatan
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <!-- Content will be loaded via AJAX -->
            <div id="previewContent">
                <div class="modal-body text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-3x text-info"></i>
                    <p class="mt-3">Memuat preview...</p>
                </div>
            </div>
        </div>
    </div>
</div>
```

---

### **Step 3: Tambahkan JavaScript Logic**

**File**: `public/js/pages/pelanggaran/create.js` (atau inline di blade)

**Lokasi**: Di bagian bawah file atau dalam `@push('scripts')`

```javascript
$(document).ready(function() {
    
    // Preview Button Click Handler
    $('#btnPreview').click(function(e) {
        e.preventDefault();
        
        // Validasi: Minimal 1 siswa dan 1 pelanggaran harus dipilih
        const siswaIds = $('input[name="siswa_id[]"]:checked').map(function() {
            return $(this).val();
        }).get();
        
        const pelanggaranIds = $('input[name="jenis_pelanggaran_id[]"]:checked').map(function() {
            return $(this).val();
        }).get();
        
        if (siswaIds.length === 0) {
            alert('Pilih minimal 1 siswa!');
            return;
        }
        
        if (pelanggaranIds.length === 0) {
            alert('Pilih minimal 1 jenis pelanggaran!');
            return;
        }
        
        // Show modal dengan loading state
        $('#previewModal').modal('show');
        $('#previewContent').html(`
            <div class="modal-body text-center py-5">
                <i class="fas fa-spinner fa-spin fa-3x text-info"></i>
                <p class="mt-3">Memuat preview...</p>
            </div>
        `);
        
        // AJAX call ke preview endpoint
        $.ajax({
            url: '{{ route("pelanggaran.preview") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                siswa_id: siswaIds,
                jenis_pelanggaran_id: pelanggaranIds
            },
            success: function(response) {
                if (response.success) {
                    // Load HTML content ke modal
                    $('#previewContent').html(response.html);
                    
                    // Handle confirm button click
                    $('#btn-confirm-submit').click(function() {
                        $('#previewModal').modal('hide');
                        // Submit form
                        $('form').submit();
                    });
                }
            },
            error: function(xhr) {
                $('#previewContent').html(`
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Gagal memuat preview. Silakan coba lagi.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    </div>
                `);
            }
        });
    });
    
    // Optional: Direct submit tanpa preview (untuk backward compatibility)
    $('#btnSubmit').click(function(e) {
        // Validasi tetap jalan
        const siswaIds = $('input[name="siswa_id[]"]:checked').length;
        const pelanggaranIds = $('input[name="jenis_pelanggaran_id[]"]:checked').length;
        
        if (siswaIds === 0 || pelanggaranIds === 0) {
            e.preventDefault();
            alert('Pilih minimal 1 siswa dan 1 jenis pelanggaran!');
        }
    });
    
});
```

---

## 🎨 UI/UX ENHANCEMENTS (Optional)

### **Enhancement 1: Disable Submit Until Preview**

Paksa user untuk preview dulu sebelum submit:

```javascript
// Disable submit button by default
$('#btnSubmit').prop('disabled', true).addClass('btn-secondary').removeClass('btn-primary');

// Enable after preview
$('#btn-confirm-submit').click(function() {
    $('#btnSubmit').prop('disabled', false).addClass('btn-primary').removeClass('btn-secondary');
    $('#previewModal').modal('hide');
    // Auto submit
    $('form').submit();
});
```

### **Enhancement 2: Show Preview Count Badge**

Tampilkan jumlah siswa & pelanggaran yang dipilih:

```javascript
function updatePreviewBadge() {
    const siswaCount = $('input[name="siswa_id[]"]:checked').length;
    const pelanggaranCount = $('input[name="jenis_pelanggaran_id[]"]:checked').length;
    
    if (siswaCount > 0 && pelanggaranCount > 0) {
        $('#btnPreview').html(`
            <i class="fas fa-eye mr-1"></i> PREVIEW DAMPAK 
            <span class="badge badge-light ml-2">${siswaCount}×${pelanggaranCount}</span>
        `);
    }
}

// Update on checkbox change
$('input[name="siswa_id[]"], input[name="jenis_pelanggaran_id[]"]').change(updatePreviewBadge);
```

### **Enhancement 3: Keyboard Shortcut**

Tambahkan keyboard shortcut untuk preview (Ctrl+P):

```javascript
$(document).keydown(function(e) {
    if (e.ctrlKey && e.key === 'p') {
        e.preventDefault();
        $('#btnPreview').click();
    }
});
```

---

## 🧪 TESTING CHECKLIST

### **Functional Testing**:
- [ ] Preview button muncul di form
- [ ] Modal muncul saat preview diklik
- [ ] Loading state tampil saat AJAX call
- [ ] Preview content tampil dengan benar
- [ ] Warning tampil untuk high-impact scenarios
- [ ] Info tampil untuk additional information
- [ ] Confirmation checkbox muncul (conditional)
- [ ] Submit button disabled sampai checkbox checked
- [ ] Form submit setelah confirm
- [ ] Error handling jika AJAX gagal

### **Edge Cases**:
- [ ] Preview tanpa pilih siswa (harus alert)
- [ ] Preview tanpa pilih pelanggaran (harus alert)
- [ ] Preview dengan 1 siswa, multiple pelanggaran
- [ ] Preview dengan multiple siswa, 1 pelanggaran
- [ ] Preview dengan siswa yang sudah punya kasus aktif
- [ ] Preview dengan siswa yang akan trigger threshold baru

### **UI/UX**:
- [ ] Modal responsive di mobile
- [ ] Loading state smooth
- [ ] Button states clear (enabled/disabled)
- [ ] Alert colors appropriate (warning=yellow, info=blue)
- [ ] Modal dapat ditutup dengan ESC atau X button

---

## 📊 EXPECTED USER FLOW

```
1. User pilih siswa & pelanggaran
   ↓
2. User klik "PREVIEW DAMPAK"
   ↓
3. Modal muncul dengan loading state
   ↓
4. AJAX call ke backend
   ↓
5. Backend evaluasi dampak (simulasi)
   ↓
6. Backend return HTML content
   ↓
7. Modal tampilkan preview:
   - Summary (total records)
   - Warnings (high impact)
   - Infos (additional info)
   - Confirmation checkbox (if needed)
   ↓
8. User baca preview
   ↓
9. IF high-impact:
      User check confirmation checkbox
      Button "Lanjutkan" enabled
   ELSE:
      Button "Lanjutkan" already enabled
   ↓
10. User klik "Lanjutkan Pencatatan"
    ↓
11. Modal close
    ↓
12. Form submit (normal flow)
```

---

## 🚀 DEPLOYMENT NOTES

### **Before Deploy**:
1. Test di local environment
2. Test dengan berbagai skenario (edge cases)
3. Test di berbagai browser (Chrome, Firefox, Edge)
4. Test di mobile (responsive)

### **After Deploy**:
1. Monitor error logs untuk AJAX failures
2. Collect user feedback
3. Monitor usage metrics (berapa % user pakai preview?)

### **Rollback Plan**:
Jika ada masalah, bisa rollback dengan:
1. Hide preview button: `$('#btnPreview').hide();`
2. Atau comment out JavaScript logic
3. Form tetap bisa submit langsung (backward compatible)

---

## 📞 SUPPORT

**Questions?**
- Check browser console untuk AJAX errors
- Check Laravel log untuk backend errors
- Check network tab untuk request/response

**Common Issues**:
- CSRF token mismatch → Refresh page
- AJAX 500 error → Check Laravel log
- Modal tidak muncul → Check jQuery loaded
- Preview tidak update → Check AJAX URL

---

**Status**: ⏳ READY FOR FRONTEND INTEGRATION  
**Estimasi**: 1 jam  
**Priority**: HIGH (Part of Phase 1)

