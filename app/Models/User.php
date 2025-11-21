<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'role_id',
        'nama',
        'username',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // =====================================================================
    // ----------------- DEFINISI RELASI ELOQUENT ------------------
    // =====================================================================

    /**
     * Relasi Wajib: User ini MILIK SATU Role.
     * (Foreign Key: role_id)
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Relasi Opsional: User (jika dia Kaprodi) MEMILIKI SATU Jurusan.
     * (Foreign Key di tabel 'jurusan': kaprodi_user_id)
     */
    public function jurusanDiampu(): HasOne
    {
        return $this->hasOne(Jurusan::class, 'kaprodi_user_id');
    }

    /**
     * Relasi Opsional: User (jika dia Wali Kelas) MEMILIKI SATU Kelas.
     * (Foreign Key di tabel 'kelas': wali_kelas_user_id)
     */
    public function kelasDiampu(): HasOne
    {
        return $this->hasOne(Kelas::class, 'wali_kelas_user_id');
    }

    /**
     * Relasi Opsional: User (jika dia Ortu) MEMILIKI BANYAK Siswa.
     * (Foreign Key di tabel 'siswa': orang_tua_user_id)
     * [UPDATED] Menggunakan HasMany agar bisa handle kakak-adik
     */
    public function anakWali(): HasMany
    {
        return $this->hasMany(Siswa::class, 'orang_tua_user_id');
    }

    /**
     * Relasi Opsional: User (jika dia Guru) TELAH MENCATAT BANYAK RiwayatPelanggaran.
     * (Foreign Key di tabel 'riwayat_pelanggaran': guru_pencatat_user_id)
     */
    public function riwayatDicatat(): HasMany
    {
        return $this->hasMany(RiwayatPelanggaran::class, 'guru_pencatat_user_id');
    }

    /**
     * Relasi Opsional: User (jika dia Penyetuju) TELAH MENYETUJUI BANYAK TindakLanjut.
     * (Foreign Key di tabel 'tindak_lanjut': penyetuju_user_id)
     */
    public function tindakLanjutDisetujui(): HasMany
    {
        return $this->hasMany(TindakLanjut::class, 'penyetuju_user_id');
    }
}