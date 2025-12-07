# 🧪 APPROVAL LOGIC - COMPREHENSIVE TEST SCENARIOS

**Tanggal**: 7 Desember 2025  
**Status**: ✅ READY FOR TESTING  
**Logic**: Pembina-Based Approval

---

## 🎯 BUSINESS RULE

```
IF "Kepala Sekolah" IN pembina_roles:
    status = "Menunggu Persetujuan"
    notification = SENT to Kepala Sekolah
ELSE:
    status = "Baru"
    notification = NOT SENT
```

---

## 📋 TEST SCENARIOS

### **Scenario 1: Wali Kelas Only (Surat 1)**

**Setup**:
```
Frequency Rule:
- Frekuensi: 6-10 kali
- Pembina: ["Wali Kelas"]
- Trigger Surat: TRUE
```

**Expected**:
- ✅ Tipe Surat: "Surat 1"
- ✅ Status: "Baru"
- ✅ Notification: NOT SENT
- ✅ Alasan: Kepala Sekolah tidak terlibat

**Test Steps**:
1. Catat pelanggaran ke-6 untuk siswa A
2. Verify tindak lanjut created dengan status "Baru"
3. Verify NO notification sent
4. Verify surat panggilan created dengan tipe "Surat 1"

---

### **Scenario 2: Wali Kelas + Kaprodi (Surat 2)**

**Setup**:
```
Frequency Rule:
- Frekuensi: 11-15 kali
- Pembina: ["Wali Kelas", "Kaprodi"]
- Trigger Surat: TRUE
```

**Expected**:
- ✅ Tipe Surat: "Surat 2"
- ✅ Status: "Baru"
- ✅ Notification: NOT SENT
- ✅ Alasan: Kepala Sekolah tidak terlibat

**Test Steps**:
1. Catat pelanggaran ke-11 untuk siswa A
2. Verify tindak lanjut updated/created dengan status "Baru"
3. Verify NO notification sent
4. Verify surat panggilan updated ke tipe "Surat 2"

---

### **Scenario 3: Wali Kelas + Kaprodi + Waka (Surat 3)**

**Setup**:
```
Frequency Rule:
- Frekuensi: 16-20 kali
- Pembina: ["Wali Kelas", "Kaprodi", "Waka Kesiswaan"]
- Trigger Surat: TRUE
```

**Expected**:
- ✅ Tipe Surat: "Surat 3"
- ✅ Status: "Baru" (CHANGED from old logic!)
- ✅ Notification: NOT SENT
- ✅ Alasan: Kepala Sekolah tidak terlibat

**Test Steps**:
1. Catat pelanggaran ke-16 untuk siswa A
2. Verify tindak lanjut updated dengan status "Baru"
3. Verify NO notification sent
4. Verify surat panggilan updated ke tipe "Surat 3"

**⚠️ BREAKING CHANGE**: Old logic would set status "Menunggu Persetujuan"

---

### **Scenario 4: All Pembina Including Kepsek (Surat 4)**

**Setup**:
```
Frequency Rule:
- Frekuensi: 21+ kali
- Pembina: ["Wali Kelas", "Kaprodi", "Waka Kesiswaan", "Kepala Sekolah"]
- Trigger Surat: TRUE
```

**Expected**:
- ✅ Tipe Surat: "Surat 4"
- ✅ Status: "Menunggu Persetujuan"
- ✅ Notification: SENT to Kepala Sekolah
- ✅ Alasan: Kepala Sekolah terlibat

**Test Steps**:
1. Catat pelanggaran ke-21 untuk siswa A
2. Verify tindak lanjut updated dengan status "Menunggu Persetujuan"
3. Verify notification SENT to Kepala Sekolah
4. Verify email received
5. Verify badge counter updated
6. Verify surat panggilan updated ke tipe "Surat 4"

---

### **Scenario 5: Edge Case - Wali Kelas + Kepsek (Surat 2)**

**Setup**:
```
Custom Frequency Rule:
- Frekuensi: 5-10 kali
- Pembina: ["Wali Kelas", "Kepala Sekolah"]
- Trigger Surat: TRUE
```

**Expected**:
- ✅ Tipe Surat: "Surat 2" (2 pembina)
- ✅ Status: "Menunggu Persetujuan" (FIXED!)
- ✅ Notification: SENT to Kepala Sekolah
- ✅ Alasan: Kepala Sekolah terlibat

**Test Steps**:
1. Catat pelanggaran ke-5 untuk siswa B
2. Verify tindak lanjut created dengan status "Menunggu Persetujuan"
3. Verify notification SENT
4. Verify surat panggilan created dengan tipe "Surat 2"

**⚠️ CRITICAL FIX**: Old logic would set status "Baru" (WRONG!)

---

### **Scenario 6: Eskalasi Without Kepsek**

**Setup**:
```
Siswa A sudah punya:
- Kasus aktif: Surat 2 (Wali Kelas + Kaprodi), Status: "Baru"

Catat pelanggaran baru yang trigger:
- Surat 3 (Wali Kelas + Kaprodi + Waka)
```

**Expected**:
- ✅ Tipe Surat: "Surat 3" (eskalasi)
- ✅ Status: "Baru" (tetap, karena tidak ada Kepsek)
- ✅ Notification: NOT SENT
- ✅ Pemicu: "... (Eskalasi)"

**Test Steps**:
1. Verify kasus existing dengan Surat 2, status "Baru"
2. Catat pelanggaran yang trigger Surat 3
3. Verify kasus updated ke Surat 3
4. Verify status tetap "Baru"
5. Verify NO notification sent

---

### **Scenario 7: Eskalasi With Kepsek**

**Setup**:
```
Siswa A sudah punya:
- Kasus aktif: Surat 3 (Wali Kelas + Kaprodi + Waka), Status: "Baru"

Catat pelanggaran baru yang trigger:
- Surat 4 (Wali Kelas + Kaprodi + Waka + Kepsek)
```

**Expected**:
- ✅ Tipe Surat: "Surat 4" (eskalasi)
- ✅ Status: "Menunggu Persetujuan" (CHANGED!)
- ✅ Notification: SENT to Kepala Sekolah
- ✅ Pemicu: "... (Eskalasi)"

**Test Steps**:
1. Verify kasus existing dengan Surat 3, status "Baru"
2. Catat pelanggaran yang trigger Surat 4
3. Verify kasus updated ke Surat 4
4. Verify status changed to "Menunggu Persetujuan"
5. Verify notification SENT

---

### **Scenario 8: Reconciliation - Downgrade Without Kepsek**

**Setup**:
```
Siswa A punya:
- Kasus aktif: Surat 4 (dengan Kepsek), Status: "Menunggu Persetujuan"

Operator hapus beberapa pelanggaran, sehingga:
- Seharusnya Surat 3 (tanpa Kepsek)
```

**Expected**:
- ✅ Tipe Surat: "Surat 3" (downgrade)
- ✅ Status: "Baru" (CHANGED from "Menunggu Persetujuan")
- ✅ Pembina Data: Updated (Kepsek removed)

**Test Steps**:
1. Verify kasus existing dengan Surat 4, status "Menunggu Persetujuan"
2. Hapus pelanggaran via operator
3. Trigger reconciliation
4. Verify kasus updated ke Surat 3
5. Verify status changed to "Baru"
6. Verify pembina data updated (no Kepsek)

---

### **Scenario 9: Reconciliation - Complete Removal**

**Setup**:
```
Siswa A punya:
- Kasus aktif: Surat 2, Status: "Baru"

Operator hapus semua pelanggaran yang trigger surat
```

**Expected**:
- ✅ Kasus: Status changed to "Selesai"
- ✅ Surat: Deleted (soft delete)
- ✅ Pemicu: "Dibatalkan otomatis setelah penyesuaian poin"

**Test Steps**:
1. Verify kasus existing dengan Surat 2
2. Hapus pelanggaran via operator
3. Trigger reconciliation
4. Verify kasus status = "Selesai"
5. Verify surat soft deleted

---

### **Scenario 10: Approval Workflow**

**Setup**:
```
Siswa A punya:
- Kasus: Surat 4 (dengan Kepsek), Status: "Menunggu Persetujuan"
```

**Test Steps - Approve**:
1. Login as Kepala Sekolah
2. Navigate to approval page
3. Verify kasus muncul di list
4. Click "Tinjau"
5. Click "Approve"
6. Verify status changed to "Disetujui"
7. Verify notification marked as read

**Test Steps - Reject**:
1. Login as Kepala Sekolah
2. Navigate to approval page
3. Click "Tinjau"
4. Click "Reject"
5. Add catatan
6. Verify status changed to "Ditolak"

---

## 🔍 VALIDATION CHECKLIST

### **Code Level**:
- [ ] `tentukanStatusBerdasarkanPembina()` method exists
- [ ] Method checks for "Kepala Sekolah" in pembina_roles
- [ ] Method returns correct status
- [ ] All calls to status determination use new method
- [ ] No hardcoded `in_array($tipeSurat, [SURAT_3, SURAT_4])`

### **Database Level**:
- [ ] Tindak lanjut created with correct status
- [ ] Surat panggilan created with correct pembina_data
- [ ] Notifications created only for "Menunggu Persetujuan"

### **UI Level**:
- [ ] Badge counter shows correct count
- [ ] Notification dropdown shows correct items
- [ ] Approval page shows only "Menunggu Persetujuan" cases
- [ ] Email sent only for "Menunggu Persetujuan"

---

## 📊 EXPECTED RESULTS SUMMARY

| Pembina Combination | Tipe Surat | Status (Old) | Status (New) | Notification |
|---------------------|------------|--------------|--------------|--------------|
| Wali Kelas | Surat 1 | Baru | Baru | ❌ |
| Wali Kelas + Kaprodi | Surat 2 | Baru | Baru | ❌ |
| Wali Kelas + Kaprodi + Waka | Surat 3 | **Menunggu** | **Baru** | ❌ |
| Wali Kelas + Kaprodi + Waka + Kepsek | Surat 4 | Menunggu | Menunggu | ✅ |
| Wali Kelas + Kepsek | Surat 2 | **Baru** | **Menunggu** | ✅ |
| Kaprodi + Kepsek | Surat 2 | **Baru** | **Menunggu** | ✅ |

**Key Changes**:
- ⚠️ Surat 3 without Kepsek: No longer requires approval
- ✅ Any surat with Kepsek: Now requires approval (edge cases fixed!)

---

## ✅ SUCCESS CRITERIA

Test dianggap sukses jika:
- ✅ All 10 scenarios pass
- ✅ No notifications sent for cases without Kepsek
- ✅ Notifications sent for all cases with Kepsek
- ✅ Status transitions follow business rules
- ✅ Edge cases handled correctly
- ✅ No regression in existing functionality

---

**Test Status**: ⏳ READY FOR EXECUTION  
**Estimated Time**: 2-3 hours  
**Priority**: 🔴 HIGH (Critical business logic change)

