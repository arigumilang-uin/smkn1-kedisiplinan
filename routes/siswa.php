<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MasterData\SiswaController;

/*
|--------------------------------------------------------------------------
| Siswa Routes
|--------------------------------------------------------------------------
|
| Routes untuk manajemen Siswa (Master Data).
| Semua routes memerlukan authentication dan authorization via Policy.
|
*/

Route::middleware(['auth'])->group(function () {
    // Siswa Resource Routes
    Route::resource('siswa', SiswaController::class)
        ->names([
            'index' => 'siswa.index',
            'create' => 'siswa.create',
            'store' => 'siswa.store',
            'show' => 'siswa.show',
            'edit' => 'siswa.edit',
            'update' => 'siswa.update',
            'destroy' => 'siswa.destroy',
        ]);

    // Additional Siswa Routes
    Route::prefix('siswa')->name('siswa.')->group(function () {
        // Export/Import
        Route::get('/export', [SiswaController::class, 'export'])
            ->name('export')
            ->middleware('can:bulkImport,App\Models\Siswa');

        Route::get('/import', [SiswaController::class, 'importForm'])
            ->name('import.form')
            ->middleware('can:bulkImport,App\Models\Siswa');

        Route::post('/import', [SiswaController::class, 'import'])
            ->name('import')
            ->middleware('can:bulkImport,App\Models\Siswa');

        // Bulk Operations
        Route::post('/bulk-delete', [SiswaController::class, 'bulkDelete'])
            ->name('bulk-delete')
            ->middleware('can:delete,App\Models\Siswa');

        // Statistics/Reports
        Route::get('/statistics', [SiswaController::class, 'statistics'])
            ->name('statistics');
    });
});
