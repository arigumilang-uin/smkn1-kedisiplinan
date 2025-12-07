<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PembinaanInternalRule;

class PembinaanInternalRulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seed default pembinaan internal rules berdasarkan TATA_TERTIB_REFERENCE.md
     * 
     * CATATAN PENTING:
     * - Pembinaan internal adalah REKOMENDASI konseling berdasarkan akumulasi poin
     * - TIDAK trigger surat pemanggilan otomatis
     * - Fokus pada pembinaan internal sekolah sebelum melibatkan orang tua
     */
    public function run(): void
    {
        $rules = [
            [
                'poin_min' => 0,
                'poin_max' => 50,
                'pembina_roles' => ['Wali Kelas'],
                'keterangan' => 'Pembinaan ringan, konseling',
                'display_order' => 1,
            ],
            [
                'poin_min' => 55,
                'poin_max' => 100,
                'pembina_roles' => ['Wali Kelas', 'Kaprodi'],
                'keterangan' => 'Pembinaan sedang, monitoring ketat',
                'display_order' => 2,
            ],
            [
                'poin_min' => 105,
                'poin_max' => 300,
                'pembina_roles' => ['Wali Kelas', 'Kaprodi', 'Waka Kesiswaan'],
                'keterangan' => 'Pembinaan intensif, evaluasi berkala',
                'display_order' => 3,
            ],
            [
                'poin_min' => 305,
                'poin_max' => 500,
                'pembina_roles' => ['Wali Kelas', 'Kaprodi', 'Waka Kesiswaan', 'Kepala Sekolah'],
                'keterangan' => 'Pembinaan kritis, pertemuan dengan orang tua',
                'display_order' => 4,
            ],
            [
                'poin_min' => 501,
                'poin_max' => null, // Open-ended
                'pembina_roles' => ['Kepala Sekolah'],
                'keterangan' => 'Dikembalikan kepada orang tua, siswa tidak dapat melanjutkan',
                'display_order' => 5,
            ],
        ];

        foreach ($rules as $rule) {
            PembinaanInternalRule::create($rule);
        }

        $this->command->info('✅ Pembinaan Internal Rules seeded successfully!');
        $this->command->info('   - 5 default rules created');
        $this->command->info('   - Range: 0-50, 55-100, 105-300, 305-500, 501+ poin');
    }
}
