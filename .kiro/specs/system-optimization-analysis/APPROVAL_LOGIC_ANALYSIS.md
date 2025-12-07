# 🔍 ANALISIS LOGIKA APPROVAL - Current vs Recommended

**Tanggal**: 7 Desember 2025  
**Issue**: Approval logic tidak konsisten dengan filosofi sistem  
**Status**: 🔴 NEEDS REFACTORING

---

## 📊 KONDISI SAAT INI (CURRENT STATE)

### **Logika Approval yang Diterapkan**:

```php
// PelanggaranRulesEngine.php line 103-106
$status = in_array($tipeSurat, [self::SURAT_3, self::SURAT_4])
    ? 'Menunggu Persetujuan'
    : 'Baru';
```

**Artinya**: Approval HANYA berdasarkan **tipe surat** (hardcoded Surat 3 & 4)

### **Logika Tipe Surat yang Diterapkan**:

```php
// PelanggaranFrequencyRule.php getSuratType()
// Surat 1: Wali Kelas (1 pembina)
// Surat 2: Wali Kelas + Kaprodi (2 pembina)
// Surat 3: Wali Kelas + Kaprodi + Waka (3 pembina)
// Surat 4: Semua pembina (4+ pembina)
```

**Artinya**: Tipe surat ditentukan oleh **jumlah & kombinasi pembina**

---

## ❌ MASALAH YANG TERIDENTIFIKASI

### **Problem 1: Logika Tidak Konsisten**

**Skenario Bermasalah**:
```
Frequency Rule A:
- Frekuensi: 10-15 kali
- Pembina: Wali Kelas + Kaprodi + Waka (3 pembina)
- Tipe Surat: Surat 3
- Trigger Surat: TRUE
→ Status: "Menunggu Persetujuan" ✅ (Kepala Sekolah terlibat)

Frequency Rule B:
- Frekuensi: 16-20 kali
- Pembina: Wali Kelas + Kaprodi (2 pembina)
- Tipe Surat: Surat 2
- Trigger Surat: TRUE
→ Status: "Baru" ❌ (Kepala Sekolah TIDAK terlibat, padahal lebih berat!)
```

**Kesimpulan**: Pelanggaran lebih berat (16-20x) malah tidak butuh approval, sedangkan yang lebih ringan (10-15x) butuh approval. **TIDAK LOGIS!**

---

### **Problem 2: Hardcoded Logic**

```php
// Hardcoded: Surat 3 & 4 butuh approval
in_array($tipeSurat, [self::SURAT_3, self::SURAT_4])
```

**Masalah**:
- Tidak fleksibel
- Tidak mempertimbangkan pembina yang terlibat
- Tidak sesuai dengan filosofi "surat fleksibel berdasarkan pembina"

---

### **Problem 3: Kepala Sekolah Tidak Konsisten**

**Filosofi Sistem**:
> "Kepala Sekolah terlibat dalam pembinaan → Butuh approval Kepala Sekolah"

**Realita Saat Ini**:
- Surat 3 (Wali Kelas + Kaprodi + Waka) → Butuh approval ✅
- Surat 4 (Semua termasuk Kepala Sekolah) → Butuh approval ✅
- **TAPI**: Logika tidak cek apakah Kepala Sekolah ada di pembina_roles!

**Contoh Edge Case**:
```
Custom Rule:
- Pembina: Wali Kelas + Kepala Sekolah (2 pembina)
- Tipe Surat: Surat 2 (karena 2 pembina)
→ Status: "Baru" ❌ (Tidak butuh approval, padahal Kepala Sekolah terlibat!)
```

---

## ✅ REKOMENDASI BEST PRACTICE

### **Prinsip Dasar**:

> **"Jika Kepala Sekolah terlibat sebagai pembina, maka WAJIB butuh approval Kepala Sekolah"**

**Alasan**:
1. ✅ **Konsisten** dengan filosofi sistem (pembina-based)
2. ✅ **Fleksibel** (tidak hardcoded tipe surat)
3. ✅ **Logis** (Kepala Sekolah approve keterlibatannya sendiri)
4. ✅ **Scalable** (mudah extend untuk role lain)

---

## 🔧 SOLUSI YANG DIREKOMENDASIKAN

### **Option 1: Approval Berdasarkan Pembina (RECOMMENDED)**

```php
/**
 * Tentukan status berdasarkan pembina yang terlibat.
 * 
 * Logic:
 * - Jika Kepala Sekolah terlibat → Menunggu Persetujuan
 * - Jika hanya Waka/Kaprodi/Wali Kelas → Baru
 * 
 * @param array $pembinaRoles
 * @return string
 */
private function tentukanStatus(array $pembinaRoles): string
{
    // Jika Kepala Sekolah terlibat sebagai pembina, butuh approval
    if (in_array('Kepala Sekolah', $pembinaRoles)) {
        return 'Menunggu Persetujuan';
    }
    
    // Jika hanya pembina level bawah, langsung proses
    return 'Baru';
}
```

**Keuntungan**:
- ✅ Konsisten dengan filosofi sistem
- ✅ Fleksibel (tidak tergantung tipe surat)
- ✅ Mudah dipahami (clear business rule)
- ✅ Scalable (bisa extend untuk role lain)

**Contoh Skenario**:
```
Rule A: Wali Kelas + Kaprodi → Surat 2 → Status: "Baru" ✅
Rule B: Wali Kelas + Kaprodi + Waka → Surat 3 → Status: "Baru" ✅
Rule C: Wali Kelas + Kaprodi + Waka + Kepsek → Surat 4 → Status: "Menunggu Persetujuan" ✅
Rule D: Wali Kelas + Kepsek → Surat 2 → Status: "Menunggu Persetujuan" ✅ (Edge case handled!)
```

---

### **Option 2: Approval Berdasarkan Severity Level (ALTERNATIVE)**

```php
/**
 * Tentukan status berdasarkan severity level.
 * 
 * Logic:
 * - Jika 4+ pembina (Surat 4) → Menunggu Persetujuan
 * - Jika 3 pembina (Surat 3) → Menunggu Persetujuan
 * - Jika 1-2 pembina → Baru
 * 
 * @param string $tipeSurat
 * @return string
 */
private function tentukanStatusBySeverity(string $tipeSurat): string
{
    // Surat 3 & 4 dianggap severe, butuh approval
    return in_array($tipeSurat, [self::SURAT_3, self::SURAT_4])
        ? 'Menunggu Persetujuan'
        : 'Baru';
}
```

**Keuntungan**:
- ✅ Simple & straightforward
- ✅ Sesuai dengan current implementation

**Kekurangan**:
- ❌ Tidak fleksibel (hardcoded)
- ❌ Tidak handle edge case (Kepsek di Surat 2)
- ❌ Tidak konsisten dengan filosofi pembina-based

---

### **Option 3: Hybrid Approach (BALANCED)**

```php
/**
 * Tentukan status dengan hybrid logic.
 * 
 * Logic:
 * 1. Jika Kepala Sekolah terlibat → Menunggu Persetujuan
 * 2. Jika 3+ pembina (severe case) → Menunggu Persetujuan
 * 3. Jika 1-2 pembina → Baru
 * 
 * @param array $pembinaRoles
 * @param string $tipeSurat
 * @return string
 */
private function tentukanStatusHybrid(array $pembinaRoles, string $tipeSurat): string
{
    // Priority 1: Jika Kepala Sekolah terlibat
    if (in_array('Kepala Sekolah', $pembinaRoles)) {
        return 'Menunggu Persetujuan';
    }
    
    // Priority 2: Jika kasus severe (3+ pembina)
    if (in_array($tipeSurat, [self::SURAT_3, self::SURAT_4])) {
        return 'Menunggu Persetujuan';
    }
    
    // Default: Langsung proses
    return 'Baru';
}
```

**Keuntungan**:
- ✅ Handle edge case (Kepsek di Surat 2)
- ✅ Tetap respect severity level
- ✅ Fleksibel & scalable

**Kekurangan**:
- ⚠️ Sedikit lebih kompleks
- ⚠️ Perlu dokumentasi yang jelas

---

## 🎯 REKOMENDASI FINAL

### **Pilihan Terbaik: Option 1 (Pembina-Based)**

**Alasan**:
1. **Konsisten** dengan filosofi sistem yang sudah ada
2. **Fleksibel** untuk berbagai kombinasi pembina
3. **Mudah dipahami** oleh stakeholder
4. **Scalable** untuk future requirements

### **Implementasi**:

```php
// PelanggaranRulesEngine.php

private function tentukanStatus(array $pembinaRoles): string
{
    // Business Rule: Jika Kepala Sekolah terlibat, butuh approval
    if (in_array('Kepala Sekolah', $pembinaRoles)) {
        return 'Menunggu Persetujuan';
    }
    
    return 'Baru';
}

// Update di processBatch()
if ($tipeSurat) {
    $pemicu = implode(', ', array_unique(array_filter($sanksiList)));
    $status = $this->tentukanStatus($pembinaRolesForSurat); // ← CHANGED
    
    $this->buatAtauUpdateTindakLanjut($siswaId, $tipeSurat, $pemicu, $status, $pembinaRolesForSurat);
}
```

---

## 📊 COMPARISON TABLE

| Kriteria | Current | Option 1 | Option 2 | Option 3 |
|----------|---------|----------|----------|----------|
| **Konsistensi** | ❌ | ✅ | ⚠️ | ✅ |
| **Fleksibilitas** | ❌ | ✅ | ❌ | ✅ |
| **Handle Edge Case** | ❌ | ✅ | ❌ | ✅ |
| **Simplicity** | ✅ | ✅ | ✅ | ⚠️ |
| **Scalability** | ❌ | ✅ | ❌ | ✅ |
| **Maintainability** | ⚠️ | ✅ | ⚠️ | ✅ |

**Winner**: **Option 1** (Pembina-Based) 🏆

---

## 🔄 MIGRATION PLAN

### **Step 1: Update Logic**
- Refactor `tentukanStatus()` method
- Update `processBatch()` call
- Update `reconcileForSiswa()` call

### **Step 2: Update Tests**
- Test dengan berbagai kombinasi pembina
- Test edge cases (Kepsek di Surat 2)
- Test backward compatibility

### **Step 3: Update Documentation**
- Update business rules documentation
- Update API documentation
- Update user guide

### **Step 4: Deploy**
- Deploy dengan feature flag (optional)
- Monitor approval workflow
- Collect feedback

---

## 🎓 BUSINESS RULES DOCUMENTATION

### **Approval Workflow Rules**:

```
IF Kepala Sekolah IN pembina_roles:
    status = "Menunggu Persetujuan"
    approver = Kepala Sekolah
    reason = "Kepala Sekolah terlibat dalam pembinaan"
ELSE:
    status = "Baru"
    approver = None
    reason = "Hanya pembina level bawah yang terlibat"
```

### **Contoh Kasus**:

| Pembina | Tipe Surat | Status | Alasan |
|---------|------------|--------|--------|
| Wali Kelas | Surat 1 | Baru | Tidak ada Kepsek |
| Wali Kelas + Kaprodi | Surat 2 | Baru | Tidak ada Kepsek |
| Wali Kelas + Kaprodi + Waka | Surat 3 | Baru | Tidak ada Kepsek |
| Wali Kelas + Kaprodi + Waka + Kepsek | Surat 4 | Menunggu Persetujuan | **Kepsek terlibat** |
| Wali Kelas + Kepsek | Surat 2 | Menunggu Persetujuan | **Kepsek terlibat** |

---

## ✅ KESIMPULAN

**Current Implementation**: ❌ Tidak konsisten dengan filosofi sistem

**Recommended Solution**: ✅ Approval berdasarkan keterlibatan Kepala Sekolah

**Impact**:
- ✅ Lebih logis & konsisten
- ✅ Lebih fleksibel
- ✅ Handle edge cases
- ✅ Mudah dipahami stakeholder

**Next Action**: Refactor `tentukanStatus()` logic dengan Option 1

