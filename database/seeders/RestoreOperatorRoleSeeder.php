<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;

class RestoreOperatorRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $targetEmail = env('RESTORE_OPERATOR_EMAIL', 'operator@smkn1.sch.id');

        $user = User::where('email', $targetEmail)->first();
        if (!$user) {
            $this->command->warn("User with email {$targetEmail} not found. Nothing to restore.");
            return;
        }

        $operatorRole = Role::where('nama_role', 'Operator Sekolah')->first();
        if (!$operatorRole) {
            $this->command->warn("Role 'Operator Sekolah' not found. Please ensure roles are seeded.");
            return;
        }

        $old = $user->role?->nama_role ?? 'NULL';
        $user->role_id = $operatorRole->id;
        $user->save();

        $this->command->info("Restored role for {$user->email}: {$old} -> Operator Sekolah");
    }
}
