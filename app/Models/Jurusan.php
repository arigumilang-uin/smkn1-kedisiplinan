<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Jurusan/Konsentrasi Keahlian Model
 * 
 * Merepresentasikan Jurusan atau Konsentrasi Keahlian di SMK.
 * Jurusan adalah child dari Program Keahlian.
 * 
 * Struktur hierarki:
 * - Program Keahlian (parent) → Jurusan/Konsentrasi (this)
 * - Kaprodi bisa mengelola jurusan melalui Program Keahlian
 */
class Jurusan extends Model
{
    use HasFactory;

    /**
     * Beri tahu Laravel bahwa tabel 'jurusan' tidak punya kolom timestamps.
     */
    public $timestamps = false;

    /**
     * Nama tabelnya adalah 'jurusan', bukan 'jurusans'.
     */
    protected $table = 'jurusan';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'program_keahlian_id',  // NEW: parent program
        'kaprodi_user_id',      // LEGACY: tetap ada untuk backward compatibility
        'nama_jurusan',
        'kode_jurusan',
        'tingkat',              // NEW: X, XI, XII
    ];

    // =====================================================================
    // RELATIONSHIPS
    // =====================================================================

    /**
     * Relasi: Jurusan dimiliki oleh Program Keahlian (parent)
     */
    public function programKeahlian(): BelongsTo
    {
        return $this->belongsTo(ProgramKeahlian::class, 'program_keahlian_id');
    }

    /**
     * Relasi Legacy: Jurusan dimiliki oleh Kaprodi (User) langsung.
     * DEPRECATED: Gunakan programKeahlian->kaprodi untuk kaprodi yang mengelola.
     * Tetap ada untuk backward compatibility.
     */
    public function kaprodi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kaprodi_user_id');
    }

    /**
     * Relasi: Jurusan memiliki banyak Kelas.
     */
    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class, 'jurusan_id');
    }

    /**
     * Relasi: Jurusan memiliki banyak Siswa melalui Kelas.
     */
    public function siswa(): HasManyThrough
    {
        return $this->hasManyThrough(Siswa::class, Kelas::class);
    }

    // =====================================================================
    // HELPER METHODS
    // =====================================================================

    /**
     * Get kaprodi yang mengelola jurusan ini.
     * Prioritas: dari programKeahlian, fallback ke kaprodi langsung.
     */
    public function getEffectiveKaprodi(): ?User
    {
        // Prioritas 1: Kaprodi dari Program Keahlian (new structure)
        if ($this->programKeahlian && $this->programKeahlian->kaprodi) {
            return $this->programKeahlian->kaprodi;
        }
        
        // Prioritas 2: Kaprodi langsung (legacy/backward compatible)
        return $this->kaprodi;
    }

    /**
     * Check apakah user adalah kaprodi yang mengelola jurusan ini.
     */
    public function isKaprodiBy(User $user): bool
    {
        // Check via Program Keahlian
        if ($this->programKeahlian && $this->programKeahlian->kaprodi_user_id === $user->id) {
            return true;
        }
        
        // Check via direct assignment (legacy)
        if ($this->kaprodi_user_id === $user->id) {
            return true;
        }
        
        return false;
    }

    /**
     * Get nama program keahlian (if exists)
     */
    public function getNamaProgramAttribute(): ?string
    {
        return $this->programKeahlian?->nama_program;
    }

    /**
     * Get full name dengan program keahlian
     */
    public function getFullNameAttribute(): string
    {
        if ($this->programKeahlian) {
            return $this->programKeahlian->nama_program . ' - ' . $this->nama_jurusan;
        }
        return $this->nama_jurusan;
    }
}