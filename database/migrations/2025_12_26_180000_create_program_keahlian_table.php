<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create 'program_keahlian' table
 * 
 * Struktur hierarki SMK:
 * - Program Keahlian (parent) → Jurusan/Konsentrasi (child)
 * - Kaprodi mengelola di level Program Keahlian
 * - Satu Program Keahlian bisa punya banyak Jurusan/Konsentrasi
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_keahlian', function (Blueprint $table) {
            $table->id();
            
            // Nama Program Keahlian (e.g., "Teknik Energi", "Rekayasa Perangkat Lunak")
            $table->string('nama_program');
            
            // Kode Program (optional, e.g., "TE", "RPL")
            $table->string('kode_program', 20)->nullable();
            
            // Kaprodi yang mengelola program ini
            $table->unsignedBigInteger('kaprodi_user_id')->nullable();
            
            // Deskripsi (optional)
            $table->text('deskripsi')->nullable();
            
            // Status aktif
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Foreign key ke users
            $table->foreign('kaprodi_user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_keahlian');
    }
};
