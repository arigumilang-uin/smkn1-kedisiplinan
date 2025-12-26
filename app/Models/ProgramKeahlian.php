<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Program Keahlian Model
 * 
 * Merepresentasikan Konsentrasi atau Sub-jurusan di SMK.
 * Program Keahlian adalah pengelompokan untuk jurusan yang memiliki
 * konsentrasi berbeda per tingkat kelas.
 * 
 * PENTING: Program Keahlian TIDAK punya Kaprodi sendiri!
 * Kaprodi dikelola di level Jurusan, dan otomatis mencakup
 * semua Program Keahlian/Konsentrasi yang tergabung.
 * 
 * Struktur:
 * - Program Keahlian = Pengelompokan Jurusan yang serupa
 * - Kaprodi = Diambil dari Jurusan yang tergabung
 */
class ProgramKeahlian extends Model
{
    use HasFactory;

    protected $table = 'program_keahlian';

    protected $fillable = [
        'nama_program',
        'kode_program',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // =====================================================================
    // RELATIONSHIPS
    // =====================================================================

    /**
     * Relasi: Program Keahlian memiliki banyak Jurusan/Konsentrasi
     */
    public function jurusan(): HasMany
    {
        return $this->hasMany(Jurusan::class, 'program_keahlian_id');
    }

    /**
     * Relasi: Program Keahlian memiliki banyak Kelas (melalui Jurusan)
     */
    public function kelas(): HasManyThrough
    {
        return $this->hasManyThrough(
            Kelas::class,
            Jurusan::class,
            'program_keahlian_id', // FK di jurusan
            'jurusan_id',          // FK di kelas
            'id',                  // PK di program_keahlian
            'id'                   // PK di jurusan
        );
    }

    /**
     * Get all siswa in this program keahlian
     */
    public function siswa()
    {
        return Siswa::whereHas('kelas.jurusan', function ($query) {
            $query->where('program_keahlian_id', $this->id);
        });
    }

    // =====================================================================
    // SCOPES
    // =====================================================================

    /**
     * Scope: Hanya program keahlian yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // =====================================================================
    // HELPER METHODS
    // =====================================================================

    /**
     * Get all jurusan IDs under this program
     */
    public function getJurusanIds(): array
    {
        return $this->jurusan()->pluck('id')->toArray();
    }

    /**
     * Get Kaprodi dari jurusan yang tergabung.
     * Kaprodi diambil dari jurusan pertama yang punya Kaprodi.
     */
    public function getKaprodi(): ?User
    {
        $jurusanWithKaprodi = $this->jurusan()
            ->whereNotNull('kaprodi_user_id')
            ->with('kaprodi')
            ->first();
        
        return $jurusanWithKaprodi?->kaprodi;
    }

    /**
     * Get Kaprodi attribute (accessor)
     */
    public function getKaprodiAttribute(): ?User
    {
        return $this->getKaprodi();
    }

    /**
     * Get siswa count
     */
    public function getSiswaCountAttribute(): int
    {
        return $this->siswa()->count();
    }

    /**
     * Get kelas count
     */
    public function getKelasCountAttribute(): int
    {
        return $this->kelas()->count();
    }
}
