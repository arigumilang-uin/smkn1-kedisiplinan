<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Simplify program_keahlian - hapus kaprodi_user_id
 * 
 * Alasan: Kaprodi sudah dikelola di level Jurusan.
 * Program Keahlian hanya untuk pengelompokan jurusan yang serupa,
 * bukan entitas yang punya Kaprodi sendiri.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('program_keahlian', function (Blueprint $table) {
            // Hapus foreign key dan kolom kaprodi_user_id
            $table->dropForeign(['kaprodi_user_id']);
            $table->dropColumn('kaprodi_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('program_keahlian', function (Blueprint $table) {
            $table->unsignedBigInteger('kaprodi_user_id')->nullable();
            $table->foreign('kaprodi_user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }
};
