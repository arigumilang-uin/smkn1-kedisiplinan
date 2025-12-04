<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // <-- 1. IMPORT DB FACADE

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Nonaktifkan Foreign Key Checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $this->call([
            RoleSeeder::class, 
            UserSeeder::class,
            JurusanSeeder::class,
            
            // KelasSeeder::class,  <-- HAPUS ATAU KOMENTARI INI (Sudah diganti MassSeeder)
            // SiswaSeeder::class,  <-- HAPUS ATAU KOMENTARI INI
            
            KategoriPelanggaranSeeder::class, 
            JenisPelanggaranSeeder::class,

            // --- SEEDER RAKSASA KITA ---
            MassSeeder::class, 
            DeveloperRoleSeeder::class,
        ]);

        // Aktifkan kembali Foreign Key Checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}