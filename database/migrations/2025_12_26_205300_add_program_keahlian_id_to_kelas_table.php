<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add program_keahlian_id to kelas table
 * 
 * PURPOSE: Allow kelas to connect to EITHER:
 * - Jurusan directly (existing jurusan_id)
 * - Program Keahlian/Konsentrasi (new program_keahlian_id)
 * 
 * LOGIC:
 * - If program_keahlian_id is set: Kelas belongs to a specific concentration
 * - If only jurusan_id is set: Kelas belongs directly to Jurusan (no concentration)
 * - jurusan_id tetap required untuk backward compatibility
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->foreignId('program_keahlian_id')
                  ->nullable()
                  ->after('jurusan_id')
                  ->constrained('program_keahlian')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropForeign(['program_keahlian_id']);
            $table->dropColumn('program_keahlian_id');
        });
    }
};
