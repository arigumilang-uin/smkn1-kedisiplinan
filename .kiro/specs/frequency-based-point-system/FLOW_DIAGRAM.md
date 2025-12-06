# Flow Diagrams: Frequency-Based Point System

## 1. Point Accumulation Flow (Sistem Baru)

```
┌─────────────────────────────────────────────────────────────────┐
│                    GURU CATAT PELANGGARAN                       │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│              Pilih Siswa & Jenis Pelanggaran                    │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│         AJAX: Preview Frequency (Real-time)                     │
│  - Current frequency: 3                                         │
│  - Next threshold: 4                                            │
│  - Poin akan ditambahkan: 25                                    │
│  - Sanksi: "Panggilan orang tua"                                │
│  - ⚠️ WARNING: Threshold akan tercapai!                         │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                      SUBMIT PENCATATAN                          │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│              PelanggaranRulesEngine::processBatch()             │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
                    ┌─────────┴─────────┐
                    │                   │
                    ▼                   ▼
        ┌───────────────────┐   ┌───────────────────┐
        │ has_frequency_    │   │ has_frequency_    │
        │ rules = TRUE?     │   │ rules = FALSE?    │
        └───────────────────┘   └───────────────────┘
                    │                   │
                    ▼                   ▼
        ┌───────────────────┐   ┌───────────────────┐
        │ evaluateFrequency │   │ Immediate         │
        │ Rules()           │   │ Accumulation      │
        │                   │   │ (Backward Compat) │
        │ - Hitung frekuensi│   │                   │
        │ - Find matched    │   │ Poin += poin      │
        │   rule            │   │ pelanggaran       │
        │ - Check threshold │   │                   │
        │   baru?           │   │                   │
        │ - Return poin     │   │                   │
        └───────────────────┘   └───────────────────┘
                    │                   │
                    └─────────┬─────────┘
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│              Hitung Total Poin Akumulasi Siswa                  │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│         tentukanTipeSuratTertinggi()                            │
│  - Dari frequency rules: Surat 1 (Wali Kelas)                  │
│  - Dari akumulasi poin: Surat 2 (55-100 poin)                  │
│  - Pilih tertinggi: Surat 2                                     │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
                    ┌─────────┴─────────┐
                    │                   │
                    ▼                   ▼
        ┌───────────────────┐   ┌───────────────────┐
        │ Ada Surat?        │   │ Tidak Ada Surat?  │
        │ (trigger_surat    │   │                   │
        │  = TRUE)          │   │ Selesai           │
        └───────────────────┘   └───────────────────┘
                    │
                    ▼
┌─────────────────────────────────────────────────────────────────┐
│         buatAtauUpdateTindakLanjut()                            │
│  - Buat TindakLanjut baru atau update existing                 │
│  - Buat SuratPanggilan dengan tipe surat                        │
│  - Set status: "Baru" atau "Menunggu Persetujuan"              │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                      SELESAI                                    │
│  ✅ Riwayat pelanggaran tercatat                                │
│  ✅ Poin siswa updated (jika threshold tercapai)                │
│  ✅ Surat pemanggilan dibuat (jika diperlukan)                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## 2. Frequency Rules Evaluation Flow

```
┌─────────────────────────────────────────────────────────────────┐
│         evaluateFrequencyRules(siswaId, pelanggaran)            │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│  Query: Hitung current frequency untuk siswa & pelanggaran      │
│  SELECT COUNT(*) FROM riwayat_pelanggaran                       │
│  WHERE siswa_id = ? AND jenis_pelanggaran_id = ?                │
│                                                                 │
│  Result: currentFrequency = 4                                   │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│  Load frequency rules untuk pelanggaran                         │
│  pelanggaran->frequencyRules                                    │
│                                                                 │
│  Rules:                                                         │
│  - Rule 1: freq 1-3 → 25 poin, Pembinaan                        │
│  - Rule 2: freq 4+ → 25 poin, Panggilan orang tua, Surat 1     │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│  Find matched rule untuk currentFrequency (4)                   │
│  rule->matchesFrequency(4)                                      │
│                                                                 │
│  Matched: Rule 2 (freq 4+)                                      │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│  Calculate previousFrequency = currentFrequency - 1 = 3         │
│  Find matched rule untuk previousFrequency (3)                  │
│                                                                 │
│  Matched: Rule 1 (freq 1-3)                                     │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│  Compare rules:                                                 │
│  - Current rule: Rule 2                                         │
│  - Previous rule: Rule 1                                        │
│  - Same rule? NO                                                │
│                                                                 │
│  ✅ THRESHOLD BARU TERCAPAI!                                    │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│  Return result:                                                 │
│  {                                                              │
│    poin_ditambahkan: 25,                                        │
│    surat_type: "Surat 1",                                       │
│    sanksi: "Panggilan orang tua dan denda..."                  │
│  }                                                              │
└─────────────────────────────────────────────────────────────────┘
```

---

## 3. Surat Pemanggilan Determination Flow

```
┌─────────────────────────────────────────────────────────────────┐
│              tentukanTipeSuratTertinggi()                       │
│  Input:                                                         │
│  - suratTypes: ["Surat 1", "Surat 2"]                          │
│  - totalPoinAkumulasi: 150                                      │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│  Extract level dari suratTypes:                                 │
│  - "Surat 1" → level 1                                          │
│  - "Surat 2" → level 2                                          │
│                                                                 │
│  Max level dari frequency rules: 2                              │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│  tentukanSuratDariAkumulasi(150)                                │
│                                                                 │
│  Range check:                                                   │
│  - 0-50: null                                                   │
│  - 55-100: Surat 2                                              │
│  - 105-300: Surat 3  ← 150 poin masuk sini                      │
│  - 305-500: Surat 4                                             │
│  - >500: Surat 4                                                │
│                                                                 │
│  Result: Surat 3 (level 3)                                      │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│  Compare levels:                                                │
│  - Max level dari frequency rules: 2                            │
│  - Level dari akumulasi: 3                                      │
│                                                                 │
│  Final max level: 3                                             │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│  Return: "Surat 3"                                              │
│                                                                 │
│  Meaning:                                                       │
│  - Pembina: Wali Kelas + Kaprodi + Waka Kesiswaan              │
│  - Status: "Menunggu Persetujuan" (Surat 3 perlu approval)     │
└─────────────────────────────────────────────────────────────────┘
```

---

## 4. Pembinaan Internal vs Surat Pemanggilan

```
┌─────────────────────────────────────────────────────────────────┐
│              DUA SISTEM YANG BERBEDA & INDEPENDEN               │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ SISTEM 1: PEMBINAAN INTERNAL (Berdasarkan Akumulasi Poin)      │
│ ═══════════════════════════════════════════════════════════════ │
│                                                                 │
│ Tujuan: Rekomendasi siapa yang harus melakukan konseling       │
│ Trigger: Total poin akumulasi siswa                            │
│ Output: TIDAK ADA SURAT, hanya pembinaan internal              │
│                                                                 │
│ Range Poin → Pembina yang Melakukan Konseling:                 │
│                                                                 │
│ ┌─────────────────────────────────────────────────────────┐   │
│ │ 0-50 poin                                               │   │
│ │ → Wali Kelas (konseling ringan)                         │   │
│ │ → Tidak ada surat                                       │   │
│ └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│ ┌─────────────────────────────────────────────────────────┐   │
│ │ 55-100 poin                                             │   │
│ │ → Wali Kelas + Kaprodi (monitoring ketat)              │   │
│ │ → Tidak ada surat                                       │   │
│ └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│ ┌─────────────────────────────────────────────────────────┐   │
│ │ 105-300 poin                                            │   │
│ │ → Wali Kelas + Kaprodi + Waka (pembinaan intensif)     │   │
│ │ → Tidak ada surat                                       │   │
│ └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│ ┌─────────────────────────────────────────────────────────┐   │
│ │ 305-500 poin                                            │   │
│ │ → Wali Kelas + Kaprodi + Waka + Kepsek (kritis)        │   │
│ │ → Tidak ada surat                                       │   │
│ └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│ ┌─────────────────────────────────────────────────────────┐   │
│ │ >500 poin                                               │   │
│ │ → Dikembalikan kepada orang tua                         │   │
│ │ → Siswa tidak dapat melanjutkan di sekolah              │   │
│ └─────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ SISTEM 2: SURAT PEMANGGILAN (Berdasarkan Pelanggaran)          │
│ ═══════════════════════════════════════════════════════════════ │
│                                                                 │
│ Tujuan: Memanggil orang tua ke sekolah untuk pembinaan         │
│ Trigger: Jenis pelanggaran + frekuensi + sanksi tertentu       │
│ Output: Surat pemanggilan orang tua                            │
│                                                                 │
│ Trigger HANYA jika sanksi mencantumkan:                        │
│ - "Panggilan orang tua" ATAU                                   │
│ - "Dikembalikan kepada orang tua"                              │
│                                                                 │
│ Tipe Surat ditentukan oleh JUMLAH PEMBINA yang terlibat:       │
│                                                                 │
│ ┌─────────────────────────────────────────────────────────┐   │
│ │ SURAT 1                                                 │   │
│ │ Pembina: Wali Kelas (1 pembina)                         │   │
│ │                                                         │   │
│ │ Contoh:                                                 │   │
│ │ - Alfa 4x → Sanksi: "Panggilan orang tua"              │   │
│ │ - Atribut 10x → Sanksi: "Panggilan orang tua"          │   │
│ └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│ ┌─────────────────────────────────────────────────────────┐   │
│ │ SURAT 2                                                 │   │
│ │ Pembina: Wali Kelas + Kaprodi (2 pembina)              │   │
│ │                                                         │   │
│ │ Contoh:                                                 │   │
│ │ - Merokok 1x → Sanksi: "Panggilan orang tua"           │   │
│ │ - Bullying 1x → Sanksi: "Panggilan orang tua"          │   │
│ └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│ ┌─────────────────────────────────────────────────────────┐   │
│ │ SURAT 3                                                 │   │
│ │ Pembina: Wali Kelas + Kaprodi + Waka (3 pembina)       │   │
│ │                                                         │   │
│ │ Contoh:                                                 │   │
│ │ - Alkohol 1x → Sanksi: "Panggilan orang tua"           │   │
│ │ - Porno aksi 1x → Sanksi: "Panggilan orang tua"        │   │
│ └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│ ┌─────────────────────────────────────────────────────────┐   │
│ │ SURAT 4                                                 │   │
│ │ Pembina: Wali Kelas + Kaprodi + Waka + Kepsek          │   │
│ │          (4 pembina)                                    │   │
│ │                                                         │   │
│ │ Contoh:                                                 │   │
│ │ - Narkoba 1x → Sanksi: "Dikembalikan kepada ortu"      │   │
│ │ - Kejahatan polisi 1x → Sanksi: "Dikembalikan..."      │   │
│ └─────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                    CONTOH KASUS NYATA                           │
└─────────────────────────────────────────────────────────────────┘

Siswa A punya total 60 poin akumulasi:

✅ SISTEM 1 (Pembinaan Internal):
   - 60 poin masuk range 55-100
   - Rekomendasi: Wali Kelas + Kaprodi harus konseling siswa
   - TIDAK ADA SURAT yang dibuat

❌ SISTEM 2 (Surat Pemanggilan):
   - Tidak ada surat otomatis karena akumulasi poin
   - Surat HANYA dibuat jika siswa melakukan pelanggaran baru
     dengan sanksi "Panggilan orang tua"

Kemudian Siswa A melakukan pelanggaran Alfa ke-4:

✅ SISTEM 2 (Surat Pemanggilan):
   - Alfa 4x → Threshold tercapai
   - Sanksi: "Panggilan orang tua dan denda..."
   - Pembina: Wali Kelas (1 pembina)
   - SURAT 1 dibuat untuk memanggil orang tua

✅ SISTEM 1 (Pembinaan Internal):
   - Total poin sekarang: 60 + 25 = 85 poin
   - Masih di range 55-100
   - Rekomendasi tetap: Wali Kelas + Kaprodi konseling

┌─────────────────────────────────────────────────────────────────┐
│                    KESIMPULAN PENTING                           │
└─────────────────────────────────────────────────────────────────┘

1. Pembinaan Internal ≠ Surat Pemanggilan
2. Akumulasi poin TIDAK trigger surat otomatis
3. Surat HANYA trigger dari pelanggaran dengan sanksi tertentu
4. Kedua sistem berjalan independen dan punya tujuan berbeda
```

---

## 5. Operator Manage Frequency Rules Flow

```
┌─────────────────────────────────────────────────────────────────┐
│              OPERATOR LOGIN & NAVIGATE                          │
│              Menu: "Kelola Frequency Rules"                     │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│              HALAMAN INDEX                                      │
│  List semua jenis pelanggaran:                                  │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ Nama Pelanggaran    │ Kategori │ Has Rules? │ Actions  │   │
│  ├─────────────────────────────────────────────────────────┤   │
│  │ Alfa                │ SEDANG   │ ✅ YES     │ [Kelola] │   │
│  │ Atribut             │ RINGAN   │ ✅ YES     │ [Kelola] │   │
│  │ Merokok             │ BERAT    │ ❌ NO      │ [Enable] │   │
│  └─────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│              KLIK "Kelola" untuk Alfa                           │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│              HALAMAN DETAIL - Frequency Rules untuk Alfa        │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ Range Frek │ Poin │ Sanksi          │ Surat? │ Actions │   │
│  ├─────────────────────────────────────────────────────────┤   │
│  │ 1-3        │ 25   │ Pembinaan       │ ❌     │ [Edit]  │   │
│  │ 4+         │ 25   │ Panggilan ortu  │ ✅     │ [Edit]  │   │
│  └─────────────────────────────────────────────────────────┘   │
│  [+ Tambah Rule]                                                │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│              KLIK "Tambah Rule"                                 │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│              MODAL FORM - Tambah Frequency Rule                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ Frequency Min: [5]                                      │   │
│  │ Frequency Max: [NULL] (open-ended)                      │   │
│  │ Poin: [25]                                              │   │
│  │ Sanksi: [Panggilan orang tua dan denda 2 pot bunga]    │   │
│  │ Trigger Surat: [✅]                                     │   │
│  │ Pembina: [☑ Wali Kelas] [☑ Kaprodi]                    │   │
│  │                                                         │   │
│  │ [Batal] [Simpan]                                        │   │
│  └─────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│              VALIDASI                                           │
│  ✅ frequency_min (5) >= 1                                      │
│  ✅ frequency_max (NULL) valid                                  │
│  ✅ poin (25) >= 0                                              │
│  ✅ sanksi tidak kosong                                         │
│  ✅ pembina_roles valid                                         │
│  ✅ Tidak ada overlap dengan rules existing:                    │
│     - Rule 1: 1-3                                               │
│     - Rule 2: 4+                                                │
│     - Rule 3 (new): 5+ → OVERLAP dengan Rule 2!                │
│                                                                 │
│  ❌ VALIDATION ERROR: Threshold overlap!                        │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│              TAMPILKAN ERROR                                    │
│  "Threshold 5+ overlap dengan rule existing (4+)"              │
│  "Silakan adjust threshold atau edit rule existing"            │
└─────────────────────────────────────────────────────────────────┘
```

---

## 6. Waka Sarana Dashboard Flow

```
┌─────────────────────────────────────────────────────────────────┐
│              WAKA SARANA LOGIN                                  │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│              DASHBOARD WAKA SARANA                              │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ STATISTIK PELANGGARAN FASILITAS                         │   │
│  │ ┌──────────────────┐  ┌──────────────────┐             │   │
│  │ │ Total Pelanggaran│  │ Bulan Ini        │             │   │
│  │ │ Fasilitas        │  │                  │             │   │
│  │ │                  │  │                  │             │   │
│  │ │      45          │  │       12         │             │   │
│  │ └──────────────────┘  └──────────────────┘             │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ RIWAYAT PELANGGARAN FASILITAS TERBARU (10 records)     │   │
│  │ ┌───────────────────────────────────────────────────┐   │   │
│  │ │ Tanggal │ Siswa │ Pelanggaran │ Pencatat │ Aksi  │   │   │
│  │ ├───────────────────────────────────────────────────┤   │   │
│  │ │ 06/12   │ Budi  │ Merusak     │ Pak Andi │ [View]│   │   │
│  │ │ 05/12   │ Siti  │ Mencoret    │ Bu Rina  │ [View]│   │   │
│  │ └───────────────────────────────────────────────────┘   │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ RIWAYAT YANG SAYA CATAT (5 records)                    │   │
│  │ ┌───────────────────────────────────────────────────┐   │   │
│  │ │ Tanggal │ Siswa │ Pelanggaran │ Aksi              │   │   │
│  │ ├───────────────────────────────────────────────────┤   │   │
│  │ │ 04/12   │ Andi  │ Merusak     │ [Edit] [Hapus]    │   │   │
│  │ └───────────────────────────────────────────────────┘   │   │
│  └─────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│              ACTIONS                                            │
│  - View: Lihat detail riwayat (read-only)                      │
│  - Edit: Edit riwayat yang dicatat sendiri                     │
│  - Hapus: Hapus riwayat yang dicatat sendiri                   │
│  - Catat Pelanggaran: Catat pelanggaran baru (semua jenis)     │
└─────────────────────────────────────────────────────────────────┘
```

---

## 7. Comparison: Old vs New System

```
┌─────────────────────────────────────────────────────────────────┐
│                    SISTEM LAMA (v1.0.0)                         │
└─────────────────────────────────────────────────────────────────┘

Alfa 4x:
  Pencatatan 1: +25 poin → Total: 25 poin
  Pencatatan 2: +25 poin → Total: 50 poin
  Pencatatan 3: +25 poin → Total: 75 poin
  Pencatatan 4: +25 poin → Total: 100 poin ❌ SALAH!

Surat: Berdasarkan poin (100 poin → Surat 2)

─────────────────────────────────────────────────────────────────

┌─────────────────────────────────────────────────────────────────┐
│                    SISTEM BARU (v2.0.0)                         │
└─────────────────────────────────────────────────────────────────┘

Alfa 4x:
  Pencatatan 1: +0 poin (threshold 1-3 belum tercapai) → Total: 0 poin
  Pencatatan 2: +0 poin (threshold 1-3 belum tercapai) → Total: 0 poin
  Pencatatan 3: +25 poin (threshold 1-3 tercapai!) → Total: 25 poin
  Pencatatan 4: +25 poin (threshold 4 tercapai!) → Total: 50 poin ✅ BENAR!

Surat: Berdasarkan pembina (Wali Kelas → Surat 1)

─────────────────────────────────────────────────────────────────

KESIMPULAN:
✅ Sistem baru lebih akurat sesuai tata tertib
✅ Poin tidak over-accumulate
✅ Surat lebih sesuai dengan hierarki pembinaan
```

---

## 8. Legend

```
┌─────────────────────────────────────────────────────────────────┐
│                          SYMBOLS                                │
└─────────────────────────────────────────────────────────────────┘

│  : Vertical flow
▼  : Flow direction
┌─ : Box start
└─ : Box end
├─ : Branch
✅ : Success / Enabled
❌ : Error / Disabled
⚠️ : Warning
→  : Result / Output
```

---

**Note**: Diagram ini dibuat dengan ASCII art untuk compatibility. Untuk diagram yang lebih visual, bisa gunakan tools seperti Mermaid, Draw.io, atau Lucidchart.
