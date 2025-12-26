<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Kelas Model
 * 
 * STRUKTUR KONEKSI (Fleksibel):
 * - jurusan_id: Koneksi langsung ke Jurusan (untuk kelas tanpa konsentrasi)
 * - program_keahlian_id: Koneksi ke Program Keahlian/Konsentrasi (opsional)
 * 
 * LOGIC:
 * - Jika program_keahlian_id terisi → Kelas ada di konsentrasi tertentu
 * - Jika hanya jurusan_id terisi → Kelas langsung di Jurusan (tanpa konsentrasi)
 */
class Kelas extends Model
{
    use HasFactory;

    /**
     * Beri tahu Laravel bahwa tabel 'kelas' tidak punya kolom timestamps.
     */
    public $timestamps = false;

    /**
     * Nama tabelnya adalah 'kelas'.
     */
    protected $table = 'kelas';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'jurusan_id',
        'program_keahlian_id',  // NEW: untuk koneksi ke konsentrasi
        'wali_kelas_user_id',
        'nama_kelas',
        'tingkat',
    ];

    // =====================================================================
    // ----------------- DEFINISI RELASI ELOQUENT ------------------
    // =====================================================================

    /**
     * Relasi: SATU Kelas DIMILIKI OLEH SATU Jurusan (langsung).
     * (Foreign Key: jurusan_id)
     */
    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }

    /**
     * Relasi: SATU Kelas DIMILIKI OLEH SATU Program Keahlian (opsional).
     * (Foreign Key: program_keahlian_id)
     */
    public function programKeahlian(): BelongsTo
    {
        return $this->belongsTo(ProgramKeahlian::class, 'program_keahlian_id');
    }

    /**
     * Relasi: SATU Kelas DIMILIKI OLEH SATU Wali Kelas (User).
     * (Foreign Key: wali_kelas_user_id)
     */
    public function waliKelas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'wali_kelas_user_id');
    }

    /**
     * Relasi: SATU Kelas MEMILIKI BANYAK Siswa.
     * (Foreign Key di tabel 'siswa': kelas_id)
     */
    public function siswa(): HasMany
    {
        return $this->hasMany(Siswa::class, 'kelas_id');
    }

    // =====================================================================
    // ----------------- HELPER METHODS ------------------
    // =====================================================================

    /**
     * Get Jurusan yang benar (baik langsung atau via Program Keahlian).
     * 
     * Priority:
     * 1. Jika ada program_keahlian_id → ambil jurusan dari program keahlian
     * 2. Fallback ke jurusan_id langsung
     */
    public function getEffectiveJurusan(): ?Jurusan
    {
        // Jika ada program keahlian, cek apakah program punya jurusan terkait
        if ($this->program_keahlian_id && $this->programKeahlian) {
            // Program keahlian mungkin punya jurusan terkait (via relasi)
            // Untuk saat ini, tetap gunakan jurusan_id langsung
        }
        
        // Return jurusan langsung
        return $this->jurusan;
    }

    /**
     * Get nama konsentrasi/program keahlian jika ada.
     */
    public function getKonsentrasiName(): ?string
    {
        return $this->programKeahlian?->nama_program;
    }

    /**
     * Check apakah kelas punya konsentrasi/program keahlian.
     */
    public function hasKonsentrasi(): bool
    {
        return !is_null($this->program_keahlian_id);
    }

    /**
     * Get display name dengan format lengkap.
     * Format: "Nama Kelas (Konsentrasi - Jurusan)" atau "Nama Kelas (Jurusan)"
     */
    public function getFullDisplayName(): string
    {
        $name = $this->nama_kelas;
        
        if ($this->hasKonsentrasi() && $this->programKeahlian) {
            $name .= " ({$this->programKeahlian->nama_program}";
            if ($this->jurusan) {
                $name .= " - {$this->jurusan->nama_jurusan}";
            }
            $name .= ")";
        } elseif ($this->jurusan) {
            $name .= " ({$this->jurusan->nama_jurusan})";
        }
        
        return $name;
    }
}