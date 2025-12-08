<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Pelanggaran\RiwayatPelanggaranController;
use App\Http\Controllers\MasterData\JenisPelanggaranController;

/*
|--------------------------------------------------------------------------
| Pelanggaran Routes
|--------------------------------------------------------------------------
|
| Routes untuk:
| - Riwayat Pelanggaran (CRUD dengan authorization & time limit)
| - Jenis Pelanggaran (Master Data)
|
*/

Route::middleware(['auth'])->group(function () {
    
    // ===================================================================
    // RIWAYAT PELANGGARAN ROUTES
    // ===================================================================
    
    Route::resource('riwayat', RiwayatPelanggaranController::class)
        ->names([
            'index' => 'riwayat.index',
            'create' => 'riwayat.create',
            'store' => 'riwayat.store',
            'show' => 'riwayat.show',
            'edit' => 'riwayat.edit',
            'update' => 'riwayat.update',
            'destroy' => 'riwayat.destroy',
        ]);

    // Additional Riwayat Routes
    Route::prefix('riwayat')->name('riwayat.')->group(function () {
        // My violations (yang saya catat)
        Route::get('/my', [RiwayatPelanggaranController::class, 'myIndex'])
            ->name('my');

        // Export
        Route::get('/export', [RiwayatPelanggaranController::class, 'export'])
            ->name('export');

        // Statistics per siswa
        Route::get('/siswa/{siswa}/statistics', [RiwayatPelanggaranController::class, 'siswaStatistics'])
            ->name('siswa.statistics');
    });

    // ===================================================================
    // JENIS PELANGGARAN ROUTES (Master Data)
    // ===================================================================
    
    Route::resource('jenis-pelanggaran', JenisPelanggaranController::class)
        ->names([
            'index' => 'jenis-pelanggaran.index',
            'create' => 'jenis-pelanggaran.create',
            'store' => 'jenis-pelanggaran.store',
            'show' => 'jenis-pelanggaran.show',
            'edit' => 'jenis-pelanggaran.edit',
            'update' => 'jenis-pelanggaran.update',
            'destroy' => 'jenis-pelanggaran.destroy',
        ])
        ->middleware('role:Operator Sekolah'); // Only operator can manage

    // Additional Jenis Pelanggaran Routes
    Route::prefix('jenis-pelanggaran')->name('jenis-pelanggaran.')->group(function () {
        // Toggle active status
        Route::post('/{id}/toggle-active', [JenisPelanggaranController::class, 'toggleActive'])
            ->name('toggle-active')
            ->middleware('role:Operator Sekolah');

        // Bulk import
        Route::post('/import', [JenisPelanggaranController::class, 'import'])
            ->name('import')
            ->middleware('role:Operator Sekolah');
    });
});
