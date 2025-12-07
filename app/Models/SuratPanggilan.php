<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SuratPanggilan extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nama tabelnya adalah 'surat_panggilan'.
     */
    protected $table = 'surat_panggilan';

    /**
     * Kita memiliki timestamps 'created_at' dan 'updated_at' di tabel ini.
     * (Default, tidak perlu ditulis)
     */

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tindak_lanjut_id',
        'nomor_surat',
        'tipe_surat',
        'pembina_data',
        'tanggal_surat',
        'tanggal_pertemuan',
        'waktu_pertemuan',
        'keperluan',
        'file_path_pdf',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'tanggal_surat' => 'date',
        'tanggal_pertemuan' => 'date',
        'pembina_data' => 'array',
    ];

    // =====================================================================
    // ----------------- DEFINISI RELASI ELOQUENT ------------------
    // =====================================================================

    /**
     * Relasi Wajib: SATU SuratPanggilan DIMILIKI OLEH SATU TindakLanjut.
     * (Foreign Key: tindak_lanjut_id)
     */
    public function tindakLanjut(): BelongsTo
    {
        return $this->belongsTo(TindakLanjut::class, 'tindak_lanjut_id');
    }
}