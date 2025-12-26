<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Add 'program_keahlian_id' to 'jurusan' table
 * 
 * Menambahkan relasi parent-child antara Program Keahlian dan Jurusan.
 * Kolom kaprodi_user_id di jurusan tetap ada untuk backward compatibility,
 * tapi Kaprodi utama akan dikelola di level program_keahlian.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jurusan', function (Blueprint $table) {
            // Tambah kolom program_keahlian_id (nullable untuk data existing)
            $table->unsignedBigInteger('program_keahlian_id')->nullable()->after('id');
            
            // Tambah kolom tingkat (optional: X, XI, XII)
            $table->string('tingkat', 10)->nullable()->after('nama_jurusan');
            
            // Foreign key ke program_keahlian
            $table->foreign('program_keahlian_id')
                  ->references('id')
                  ->on('program_keahlian')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('jurusan', function (Blueprint $table) {
            $table->dropForeign(['program_keahlian_id']);
            $table->dropColumn(['program_keahlian_id', 'tingkat']);
        });
    }
};
