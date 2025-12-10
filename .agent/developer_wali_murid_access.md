# Testing Wali Murid Dashboard as Developer

**Issue:** Developer role cannot access Wali Murid dashboard  
**Solution:** Link developer user to a siswa as wali murid

---

## 🔍 **HOW IT WORKS**

Wali Murid Dashboard uses relationship: `User.anakWali()` → `Siswa.wali_murid_user_id`

For Developer to access, you need to:
1. Find your Developer user ID
2. Update a siswa's `wali_murid_user_id` to your user ID

---

## 📝 **STEP-BY-STEP GUIDE**

### **Step 1: Find Your Developer User ID**

```bash
php artisan tinker

# In tinker:
$dev = \App\Models\User::where('username', 'YOURDEVUSERNAME')->first();
echo "Developer User ID: " . $dev->id;
echo "\nRole: " . $dev->role->nama_role;

# Example output:
# Developer User ID: 1
# Role: Developer
```

### **Step 2: Choose a Siswa to Test With**

```bash
# Still in tinker:
$siswa = \App\Models\Siswa::first(); // Or find specific one
echo "Siswa ID: " . $siswa->id;
echo "\nNama: " . $siswa->nama_siswa;
echo "\nCurrent wali_murid_user_id: " . $siswa->wali_murid_user_id;
```

### **Step 3: Link Developer User to Siswa**

```bash
# Still in tinker:
$siswa->wali_murid_user_id = $dev->id;
$siswa->save();

echo "✅ Linked! Developer can now see this siswa's dashboard";
```

**Alternative - Direct SQL:**
```sql
UPDATE siswa 
SET wali_murid_user_id = 1  -- Your developer user ID
WHERE id = 95;  -- The siswa you want to test with
```

### **Step 4: Access Dashboard**

Navigate to:
```
http://127.0.0.1:8000/dashboard/wali_murid
```

You should now see the dashboard for siswa 95!

---

## 🚨 **IMPORTANT NOTES**

### **1. Multiple Children Support**

The system supports 1 wali murid having multiple children:

```bash
# Link multiple siswa to same wali:
$anak1 = \App\Models\Siswa::find(95);
$anak1->wali_murid_user_id = 1;
$anak1->save();

$anak2 = \App\Models\Siswa::find(96);
$anak2->wali_murid_user_id = 1;
$anak2->save();

# Dashboard will show dropdown to switch between children
```

### **2. Auto-Sync Name**

⚠️ **WARNING:** If siswa has `wali_murid_user_id` set, the system might try to auto-sync the user's `nama`:

```php
// UserObserver might update user.nama to match siswa.nama_wali_murid
// For Developer, you might want to disable this
```

If your Developer user's name gets changed after linking, this is the auto-sync feature.

### **3. Unlinking After Test**

```bash
php artisan tinker

# Unlink to restore:
$siswa = \App\Models\Siswa::find(95);
$siswa->wali_murid_user_id = null;
$siswa->save();
```

---

## 🔧 **ALTERNATIVE: Temporary Link Command**

Create a quick artisan command for testing:

```bash
php artisan make:command DevLinkWaliMurid
```

**Command Code:**
```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Siswa;

class DevLinkWaliMurid extends Command
{
    protected $signature = 'dev:link-wali {user_id} {siswa_id}';
    protected $description = 'Link developer user to siswa for testing wali murid dashboard';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $siswaId = $this->argument('siswa_id');
        
        $user = User::find($userId);
        $siswa = Siswa::find($siswaId);
        
        if (!$user || !$siswa) {
            $this->error('User or Siswa not found!');
            return 1;
        }
        
        $siswa->wali_murid_user_id = $userId;
        $siswa->save();
        
        $this->info("✅ Linked {$user->username} to {$siswa->nama_siswa}");
        $this->info("Access: /dashboard/wali_murid");
        
        return 0;
    }
}
```

**Usage:**
```bash
php artisan dev:link-wali 1 95
# Links user ID 1 to siswa ID 95
```

---

## 🐛 **COMMON ISSUES**

### **Issue 1: "No Data" Page Shown**

**Cause:** No siswa linked (empty `anakWali` collection)

**Fix:** Make sure `siswa.wali_murid_user_id = your_user_id`

---

### **Issue 2: Poin Shows 0**

**Cause:** OLD bug in dashboard (now fixed!)

**Fix:** Applied in this session - dashboard now uses `PelanggaranService->calculateTotalPoin()`

---

### **Issue 3: Name Changed After Link**

**Cause:** Auto-sync observer changed user nama

**Fix:** 
```bash
# Restore your name:
$user = \App\Models\User::find(1);
$user->nama = 'Developer';
$user->save();
```

Or disable observer temporarily.

---

## ✅ **QUICK TEST SCRIPT**

```bash
php artisan tinker --execute="
\$dev = \App\Models\User::find(1);
\$siswa = \App\Models\Siswa::find(95);
\$siswa->wali_murid_user_id = \$dev->id;
\$siswa->save();
echo 'Linked! Go to: http://127.0.0.1:8000/dashboard/wali_murid';
"
```

---

## 📋 **SUMMARY**

To access Wali Murid dashboard as Developer:

1. ✅ Find your user ID
2. ✅ Update `siswa.wali_murid_user_id` to your ID
3. ✅ Access `/dashboard/wali_murid`
4. ✅ See siswa's data!

**Status:** Ready to test! 🎉
