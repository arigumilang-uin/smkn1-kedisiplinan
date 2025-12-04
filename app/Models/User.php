<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Services\RoleService;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable, LogsActivity;

    /**
     * Configure activity log options for User model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nama', 'username', 'email', 'role_id'])
            ->useLogName('user')
            ->logOnlyDirty();
    }

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
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

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
     * Relasi Opsional: User (jika dia Wali Murid) MEMILIKI BANYAK Siswa.
     * (Foreign Key di tabel 'siswa': wali_murid_user_id)
     * [UPDATED] Menggunakan HasMany agar bisa handle kakak-adik
     */
    public function anakWali(): HasMany
    {
        return $this->hasMany(Siswa::class, 'wali_murid_user_id');
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

    /**
     * Check if the user has the given role name.
     * Accepts a single role string or an array of role names.
     *
     * @param string|array $roles
     * @return bool
     */
    public function hasRole(string|array $roles): bool
    {
        return RoleService::hasRole($roles, $this);
    }

    /**
     * Whether this user record is the Developer account (actual role), and allowed in non-production.
     */
    public function isDeveloper(): bool
    {
        return RoleService::isRealDeveloper($this);
    }

    /**
     * Return the effective role name for this user considering developer impersonation override.
     * If the user is Developer and an override is set in session, return the override.
     * Otherwise return the actual role name (or null if none).
     *
     * @return string|null
     */
    public function effectiveRoleName(): ?string
    {
        return RoleService::effectiveRoleName($this);
    }

    /**
     * Shorthand to check any role in array.
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->hasRole($roles);
    }

    /**
     * Roles considered as teachers (allowed to record pelanggaran)
     *
     * @var array
     */
    protected static array $TEACHER_ROLES = [
        'Guru',
        'Wali Kelas',
        'Kaprodi',
        'Waka Kesiswaan',
        'Operator Sekolah',
    ];

    /**
     * Whether the user is a teacher (can record violations).
     */
    public function isTeacher(): bool
    {
        return $this->hasAnyRole(self::$TEACHER_ROLES);
    }

    /**
     * Check if user is Wali Kelas.
     */
    public function isWaliKelas(): bool
    {
        return $this->hasRole('Wali Kelas');
    }

    /**
     * Check if user is Kaprodi.
     */
    public function isKaprodi(): bool
    {
        return $this->hasRole('Kaprodi');
    }

    /**
     * Check if user is Wali Murid (parent/guardian).
     */
    public function isWaliMurid(): bool
    {
        return $this->hasRole('Wali Murid');
    }

    /**
     * Defensive: whether a user is allowed to record a violation for given student.
     * Teachers can record for any student; other roles cannot.
     */
    public function canRecordFor(Siswa $siswa): bool
    {
        return $this->isTeacher();
    }

    /**
    * Whether the user can view the given student's records.
    * - Wali Kelas: only students in their class
    * - Kaprodi: only students in their jurusan
    * - Wali Murid: only their children
    * - Others (Operator, Waka, Kepala Sekolah): can view all
     */
    public function canViewStudent(Siswa $siswa): bool
    {
        if ($this->isWaliKelas()) {
            $kelas = $this->kelasDiampu;
            return $kelas && $siswa->kelas_id === $kelas->id;
        }

        if ($this->isKaprodi()) {
            $jurusan = $this->jurusanDiampu;
            return $jurusan && $siswa->kelas->jurusan_id === $jurusan->id;
        }

        if ($this->isWaliMurid()) {
            return $this->anakWali->pluck('id')->contains($siswa->id);
        }

        // Default: allow
        return true;
    }
}