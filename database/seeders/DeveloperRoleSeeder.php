<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\User;

class DeveloperRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat role Developer jika belum ada
        $roleName = 'Developer';
        $role = Role::firstOrCreate(['nama_role' => $roleName]);

        // Coba ambil email admin dev dari ENV
        $devEmail = env('DEV_ADMIN_EMAIL');

        if ($devEmail) {
            $user = User::where('email', $devEmail)->first();

            if ($user) {
                // Jika user sudah memiliki role dan bukan Developer, jangan menimpa.
                if ($user->role && $user->role->nama_role !== $roleName) {
                    $this->command->warn("User {$user->email} sudah memiliki role '{$user->role->nama_role}'. Seeder tidak akan menimpa role tersebut.");
                    $this->command->info("Jika Anda ingin akun ini menjadi Developer, ubah role secara manual atau hapus role terlebih dahulu.");
                    return;
                }

                // Assign jika belum Developer
                $user->role_id = $role->id;
                $user->save();
                $this->command->info("Assigned role '{$roleName}' to existing user: {$user->email}");
                return;
            }

            // Jika user tidak ditemukan, buat akun developer baru (aman untuk dev)
            $password = bcrypt(env('DEV_ADMIN_PASSWORD', 'password'));
            $new = User::create([
                'role_id' => $role->id,
                'nama' => 'Developer',
                'username' => explode('@', $devEmail)[0],
                'email' => $devEmail,
                'password' => $password,
            ]);
            $this->command->info("Created new developer user: {$new->email}");
            return;
        }

        // Jika tidak ada DEV_ADMIN_EMAIL, buat akun developer lokal baru agar tidak menimpa user pertama
        $localEmail = 'developer@local';
        $existing = User::where('email', $localEmail)->first();
        if ($existing) {
            if ($existing->role && $existing->role->nama_role !== $roleName) {
                $this->command->warn("Local user {$localEmail} exists with role '{$existing->role->nama_role}'. Seeder akan mengatur role ke Developer.");
            }
            $existing->role_id = $role->id;
            $existing->save();
            $this->command->info("Assigned role '{$roleName}' to local user: {$existing->email}");
            return;
        }

        $new = User::create([
            'role_id' => $role->id,
            'nama' => 'Developer',
            'username' => 'developer',
            'email' => $localEmail,
            'password' => bcrypt(env('DEV_ADMIN_PASSWORD', 'password')),
        ]);
        $this->command->info("Created new local developer user: {$new->email}");
    }
}
