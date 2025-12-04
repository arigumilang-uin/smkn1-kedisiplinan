<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisPelanggaran extends Model
{
    use HasFactory;

    /**
     * Beri tahu Laravel bahwa tabel ini tidak punya timestamps.
     */
    public $timestamps = false;

    /**
     * Nama tabelnya adalah 'jenis_pelanggaran'.
     */
    protected $table = 'jenis_pelanggaran';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kategori_id',
        'nama_pelanggaran',
        'poin',
        'filter_category',
        'keywords',
    ];

    // =====================================================================
    // ----------------- DEFINISI RELASI ELOQUENT ------------------
    // =====================================================================

    /**
     * Relasi Wajib: SATU JenisPelanggaran DIMILIKI OLEH SATU KategoriPelanggaran.
     * (Foreign Key: kategori_id)
     */
    public function kategoriPelanggaran(): BelongsTo
    {
        return $this->belongsTo(KategoriPelanggaran::class, 'kategori_id');
    }

    /**
     * Relasi Wajib: SATU JenisPelanggaran TELAH TERCATAT BANYAK KALI di RiwayatPelanggaran.
     * (Foreign Key di tabel 'riwayat_pelanggaran': jenis_pelanggaran_id)
     */
    public function riwayatPelanggaran(): HasMany
    {
        return $this->hasMany(RiwayatPelanggaran::class, 'jenis_pelanggaran_id');
    }

    // =====================================================================
    // ----------------------- QUERY SCOPES -----------------------
    // =====================================================================

    /**
     * Scope: Filter berdasarkan kategori filter (atribut, absensi, kerapian, ibadah, berat).
     */
    public function scopeByFilterCategory($query, $category)
    {
        if ($category) {
            $query->where('filter_category', $category);
        }
        return $query;
    }

    /**
     * Scope: Pencarian fuzzy berdasarkan keyword/alias atau nama pelanggaran.
     * Menggunakan logika pencarian yang fleksibel:
     * - Cek kesamaan dengan nama pelanggaran (LIKE)
     * - Cek kesamaan dengan keywords yang tersimpan
     */
    public function scopeSearchByKeyword($query, $keyword)
    {
        if (!$keyword) {
            return $query;
        }

        $keyword = strtolower(trim($keyword));

        return $query->where(function($q) use ($keyword) {
            // Pencarian pada nama pelanggaran
            $q->whereRaw("LOWER(nama_pelanggaran) LIKE ?", ["%{$keyword}%"])
              // Pencarian pada keywords (yang dipisahkan dengan |)
              ->orWhereRaw("LOWER(keywords) LIKE ?", ["%{$keyword}%"]);
        });
    }

    // =====================================================================
    // ----------------------- HELPER METHODS -----------------------
    // =====================================================================

    /**
     * Kembalikan daftar keywords sebagai array.
     * Keywords disimpan dalam format: "keyword1|keyword2|keyword3"
     */
    public function getKeywordsArray(): array
    {
        if (!$this->keywords) {
            return [];
        }
        return array_filter(array_map('trim', explode('|', $this->keywords)));
    }

    /**
     * Set keywords dari array, akan disimpan dengan pemisah |
     */
    public function setKeywordsAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['keywords'] = implode('|', array_filter($value));
        } else {
            $this->attributes['keywords'] = $value;
        }
    }
}