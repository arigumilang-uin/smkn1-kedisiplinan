# Test Gap Logic - Pembinaan Internal Rules

## Konsep Gap Handling

**UI**: Menampilkan gap sesuai tata tertib (0-50, 55-100, 105-300, 305-500)
**Sistem**: Otomatis isi gap dengan rule sebelumnya

### Logic:
Sistem mencari rule terakhir yang `poin_min <= totalPoin`, kemudian:
1. Jika `totalPoin <= poin_max` → Match!
2. Jika `poin_max = null` (open-ended) → Match!
3. Jika `totalPoin > poin_max` DAN tidak ada rule berikutnya → Match! (handle gap)
4. Jika `totalPoin > poin_max` DAN `totalPoin < poin_min_rule_berikutnya` → Match! (handle gap)

---

## Test Scenarios

### Default Rules (Seeded):
```
Rule 1: 0-50 poin → Wali Kelas
Rule 2: 55-100 poin → Wali Kelas + Kaprodi
Rule 3: 105-300 poin → Wali Kelas + Kaprodi + Waka
Rule 4: 305-500 poin → Semua pembina
Rule 5: 501+ poin → Kepala Sekolah
```

---

## Test Cases

### Test 1: Poin dalam range normal
```php
$rulesEngine = new \App\Services\PelanggaranRulesEngine();

// Test poin 25 (dalam range 0-50)
$rekomendasi = $rulesEngine->getPembinaanInternalRekomendasi(25);
// Expected: Rule 1 (0-50 poin, Wali Kelas)

// Test poin 75 (dalam range 55-100)
$rekomendasi = $rulesEngine->getPembinaanInternalRekomendasi(75);
// Expected: Rule 2 (55-100 poin, Wali Kelas + Kaprodi)

// Test poin 200 (dalam range 105-300)
$rekomendasi = $rulesEngine->getPembinaanInternalRekomendasi(200);
// Expected: Rule 3 (105-300 poin, Wali Kelas + Kaprodi + Waka)
```

---

### Test 2: Poin dalam GAP (51-54)
```php
$rulesEngine = new \App\Services\PelanggaranRulesEngine();

// Test poin 51 (gap antara 0-50 dan 55-100)
$rekomendasi = $rulesEngine->getPembinaanInternalRekomendasi(51);
// Expected: Rule 1 (0-50 poin, Wali Kelas)
// Reason: 51 > 50 (poin_max rule 1), tapi 51 < 55 (poin_min rule 2)
//         Maka gunakan rule 1 untuk handle gap

// Test poin 52
$rekomendasi = $rulesEngine->getPembinaanInternalRekomendasi(52);
// Expected: Rule 1 (0-50 poin, Wali Kelas)

// Test poin 53
$rekomendasi = $rulesEngine->getPembinaanInternalRekomendasi(53);
// Expected: Rule 1 (0-50 poin, Wali Kelas)

// Test poin 54
$rekomendasi = $rulesEngine->getPembinaanInternalRekomendasi(54);
// Expected: Rule 1 (0-50 poin, Wali Kelas)
```

---

### Test 3: Poin dalam GAP (101-104)
```php
$rulesEngine = new \App\Services\PelanggaranRulesEngine();

// Test poin 101 (gap antara 55-100 dan 105-300)
$rekomendasi = $rulesEngine->getPembinaanInternalRekomendasi(101);
// Expected: Rule 2 (55-100 poin, Wali Kelas + Kaprodi)
// Reason: 101 > 100 (poin_max rule 2), tapi 101 < 105 (poin_min rule 3)
//         Maka gunakan rule 2 untuk handle gap

// Test poin 102
$rekomendasi = $rulesEngine->getPembinaanInternalRekomendasi(102);
// Expected: Rule 2 (55-100 poin, Wali Kelas + Kaprodi)

// Test poin 103
$rekomendasi = $rulesEngine->getPembinaanInternalRekomendasi(103);
// Expected: Rule 2 (55-100 poin, Wali Kelas + Kaprodi)

// Test poin 104
$rekomendasi = $rulesEngine->getPembinaanInternalRekomendasi(104);
// Expected: Rule 2 (55-100 poin, Wali Kelas + Kaprodi)
```

---

### Test 4: Poin dalam GAP (301-304)
```php
$rulesEngine = new \App\Services\PelanggaranRulesEngine();

// Test poin 301 (gap antara 105-300 dan 305-500)
$rekomendasi = $rulesEngine->getPembinaanInternalRekomendasi(301);
// Expected: Rule 3 (105-300 poin, Wali Kelas + Kaprodi + Waka)
// Reason: 301 > 300 (poin_max rule 3), tapi 301 < 305 (poin_min rule 4)
//         Maka gunakan rule 3 untuk handle gap

// Test poin 302
$rekomendasi = $rulesEngine->getPembinaanInternalRekomendasi(302);
// Expected: Rule 3 (105-300 poin, Wali Kelas + Kaprodi + Waka)

// Test poin 303
$rekomendasi = $rulesEngine->getPembinaanInternalRekomendasi(303);
// Expected: Rule 3 (105-300 poin, Wali Kelas + Kaprodi + Waka)

// Test poin 304
$rekomendasi = $rulesEngine->getPembinaanInternalRekomendasi(304);
// Expected: Rule 3 (105-300 poin, Wali Kelas + Kaprodi + Waka)
```

---

### Test 5: Poin di boundary (tepat di batas)
```php
$rulesEngine = new \App\Services\PelanggaranRulesEngine();

// Test poin 50 (batas atas rule 1)
$rekomendasi = $rulesEngine->getPembinaanInternalRekomendasi(50);
// Expected: Rule 1 (0-50 poin, Wali Kelas)

// Test poin 55 (batas bawah rule 2)
$rekomendasi = $rulesEngine->getPembinaanInternalRekomendasi(55);
// Expected: Rule 2 (55-100 poin, Wali Kelas + Kaprodi)

// Test poin 100 (batas atas rule 2)
$rekomendasi = $rulesEngine->getPembinaanInternalRekomendasi(100);
// Expected: Rule 2 (55-100 poin, Wali Kelas + Kaprodi)

// Test poin 105 (batas bawah rule 3)
$rekomendasi = $rulesEngine->getPembinaanInternalRekomendasi(105);
// Expected: Rule 3 (105-300 poin, Wali Kelas + Kaprodi + Waka)

// Test poin 300 (batas atas rule 3)
$rekomendasi = $rulesEngine->getPembinaanInternalRekomendasi(300);
// Expected: Rule 3 (105-300 poin, Wali Kelas + Kaprodi + Waka)

// Test poin 305 (batas bawah rule 4)
$rekomendasi = $rulesEngine->getPembinaanInternalRekomendasi(305);
// Expected: Rule 4 (305-500 poin, Semua pembina)

// Test poin 500 (batas atas rule 4)
$rekomendasi = $rulesEngine->getPembinaanInternalRekomendasi(500);
// Expected: Rule 4 (305-500 poin, Semua pembina)

// Test poin 501 (batas bawah rule 5, open-ended)
$rekomendasi = $rulesEngine->getPembinaanInternalRekomendasi(501);
// Expected: Rule 5 (501+ poin, Kepala Sekolah)
```

---

### Test 6: Poin sangat tinggi (open-ended)
```php
$rulesEngine = new \App\Services\PelanggaranRulesEngine();

// Test poin 600
$rekomendasi = $rulesEngine->getPembinaanInternalRekomendasi(600);
// Expected: Rule 5 (501+ poin, Kepala Sekolah)

// Test poin 1000
$rekomendasi = $rulesEngine->getPembinaanInternalRekomendasi(1000);
// Expected: Rule 5 (501+ poin, Kepala Sekolah)

// Test poin 9999
$rekomendasi = $rulesEngine->getPembinaanInternalRekomendasi(9999);
// Expected: Rule 5 (501+ poin, Kepala Sekolah)
```

---

### Test 7: Poin 0 atau negatif
```php
$rulesEngine = new \App\Services\PelanggaranRulesEngine();

// Test poin 0
$rekomendasi = $rulesEngine->getPembinaanInternalRekomendasi(0);
// Expected: Rule 1 (0-50 poin, Wali Kelas)

// Test poin negatif (edge case, seharusnya tidak terjadi)
$rekomendasi = $rulesEngine->getPembinaanInternalRekomendasi(-10);
// Expected: Tidak ada pembinaan (karena tidak ada rule dengan poin_min negatif)
```

---

## Manual Testing Script

Jalankan di `php artisan tinker`:

```php
$rulesEngine = new \App\Services\PelanggaranRulesEngine();

// Test semua gap scenarios
$testCases = [
    0, 25, 50,           // Rule 1
    51, 52, 53, 54,      // Gap 1 (should use Rule 1)
    55, 75, 100,         // Rule 2
    101, 102, 103, 104,  // Gap 2 (should use Rule 2)
    105, 200, 300,       // Rule 3
    301, 302, 303, 304,  // Gap 3 (should use Rule 3)
    305, 400, 500,       // Rule 4
    501, 600, 1000       // Rule 5
];

foreach ($testCases as $poin) {
    $rekomendasi = $rulesEngine->getPembinaanInternalRekomendasi($poin);
    echo sprintf(
        "Poin %4d → %s | %s\n",
        $poin,
        $rekomendasi['range_text'],
        implode(', ', $rekomendasi['pembina_roles'])
    );
}
```

**Expected Output:**
```
Poin    0 → 0-50 poin | Wali Kelas
Poin   25 → 0-50 poin | Wali Kelas
Poin   50 → 0-50 poin | Wali Kelas
Poin   51 → 0-50 poin | Wali Kelas (GAP HANDLED)
Poin   52 → 0-50 poin | Wali Kelas (GAP HANDLED)
Poin   53 → 0-50 poin | Wali Kelas (GAP HANDLED)
Poin   54 → 0-50 poin | Wali Kelas (GAP HANDLED)
Poin   55 → 55-100 poin | Wali Kelas, Kaprodi
Poin   75 → 55-100 poin | Wali Kelas, Kaprodi
Poin  100 → 55-100 poin | Wali Kelas, Kaprodi
Poin  101 → 55-100 poin | Wali Kelas, Kaprodi (GAP HANDLED)
Poin  102 → 55-100 poin | Wali Kelas, Kaprodi (GAP HANDLED)
Poin  103 → 55-100 poin | Wali Kelas, Kaprodi (GAP HANDLED)
Poin  104 → 55-100 poin | Wali Kelas, Kaprodi (GAP HANDLED)
Poin  105 → 105-300 poin | Wali Kelas, Kaprodi, Waka Kesiswaan
Poin  200 → 105-300 poin | Wali Kelas, Kaprodi, Waka Kesiswaan
Poin  300 → 105-300 poin | Wali Kelas, Kaprodi, Waka Kesiswaan
Poin  301 → 105-300 poin | Wali Kelas, Kaprodi, Waka Kesiswaan (GAP HANDLED)
Poin  302 → 105-300 poin | Wali Kelas, Kaprodi, Waka Kesiswaan (GAP HANDLED)
Poin  303 → 105-300 poin | Wali Kelas, Kaprodi, Waka Kesiswaan (GAP HANDLED)
Poin  304 → 105-300 poin | Wali Kelas, Kaprodi, Waka Kesiswaan (GAP HANDLED)
Poin  305 → 305-500 poin | Wali Kelas, Kaprodi, Waka Kesiswaan, Kepala Sekolah
Poin  400 → 305-500 poin | Wali Kelas, Kaprodi, Waka Kesiswaan, Kepala Sekolah
Poin  500 → 305-500 poin | Wali Kelas, Kaprodi, Waka Kesiswaan, Kepala Sekolah
Poin  501 → 501+ poin | Kepala Sekolah
Poin  600 → 501+ poin | Kepala Sekolah
Poin 1000 → 501+ poin | Kepala Sekolah
```

---

## Kesimpulan

Logic gap handling berhasil diimplementasi dengan:
- ✅ UI tetap menampilkan gap sesuai tata tertib
- ✅ Sistem otomatis handle gap dengan rule sebelumnya
- ✅ Tidak ada poin yang "jatuh" ke kondisi tidak ada pembinaan
- ✅ Logic konsisten dan mudah dipahami

**Status**: ✅ READY FOR TESTING
