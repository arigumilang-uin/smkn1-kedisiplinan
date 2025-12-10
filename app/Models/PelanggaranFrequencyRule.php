<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PelanggaranFrequencyRule extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'pelanggaran_frequency_rules';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'jenis_pelanggaran_id',
        'frequency_min',
        'frequency_max',
        'poin',
        'sanksi_description',
        'trigger_surat',
        'pembina_roles',
        'display_order',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'trigger_surat' => 'boolean',
        'pembina_roles' => 'array',
    ];

    // =====================================================================
    // ----------------- DEFINISI RELASI ELOQUENT ------------------
    // =====================================================================

    /**
     * Relasi ke JenisPelanggaran.
     */
    public function jenisPelanggaran(): BelongsTo
    {
        return $this->belongsTo(JenisPelanggaran::class, 'jenis_pelanggaran_id');
    }

    // =====================================================================
    // ----------------------- HELPER METHODS -----------------------
    // =====================================================================

    /**
     * Cek apakah frekuensi TEPAT SAMA dengan threshold rule ini.
     * 
     * LOGIC (Updated):
     * - Rule dengan frequency_max: Trigger di MAX (bukan di min-max range)
     * - Rule tanpa frequency_max (exact): Trigger di MIN
     * 
     * Contoh:
     * - Min=1, Max=3: Trigger HANYA di frek 3 (bukan 1,2,3)
     * - Min=1, Max=NULL: Trigger di frek 1 (exact)
     * 
     * Rationale:
     * - Min-Max defines threshold RANGE
     * - Poin applied ONLY when REACHING the threshold (MAX)
     * - Before threshold: recorded but no poin added
     */
    public function matchesFrequency(int $frequency): bool
    {
        if ($this->frequency_max === null) {
            // Exact match: trigger at MIN
            return $frequency === $this->frequency_min;
        }

        // Range: trigger ONLY at MAX (threshold)
        return $frequency === $this->frequency_max;
    }

    /**
     * Tentukan tipe surat berdasarkan pembina yang terlibat.
     * 
     * CATATAN: "Semua Guru & Staff" biasanya untuk pembinaan ditempat, tidak trigger surat formal.
     * 
     * @return string|null Tipe surat (Surat 1, Surat 2, Surat 3, Surat 4) atau null
     */
    public function getSuratType(): ?string
    {
        if (!$this->trigger_surat) {
            return null;
        }

        // Jika pembina adalah "Semua Guru & Staff", tidak trigger surat formal
        // (pembinaan ditempat oleh siapa saja yang melihat)
        if (in_array('Semua Guru & Staff', $this->pembina_roles)) {
            return null;
        }

        $pembinaCount = count($this->pembina_roles);

        // Surat 1: Wali Kelas (1 pembina)
        if ($pembinaCount === 1 && in_array('Wali Kelas', $this->pembina_roles)) {
            return 'Surat 1';
        }

        // Surat 2: Wali Kelas + Kaprodi (2 pembina)
        if ($pembinaCount === 2 &&
            in_array('Wali Kelas', $this->pembina_roles) &&
            in_array('Kaprodi', $this->pembina_roles)) {
            return 'Surat 2';
        }

        // Surat 3: Wali Kelas + Kaprodi + Waka (3 pembina)
        if ($pembinaCount === 3 &&
            in_array('Wali Kelas', $this->pembina_roles) &&
            in_array('Kaprodi', $this->pembina_roles) &&
            in_array('Waka Kesiswaan', $this->pembina_roles)) {
            return 'Surat 3';
        }

        // Surat 4: Semua pembina (4 pembina atau lebih)
        if ($pembinaCount >= 4) {
            return 'Surat 4';
        }

        return null;
    }
}
