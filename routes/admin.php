<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Rules\FrequencyRulesController;
use App\Http\Controllers\Rules\PembinaanInternalRulesController;
use App\Http\Controllers\Rules\RulesEngineSettingsController;
use App\Http\Controllers\Audit\ActivityLogController;

/*
|--------------------------------------------------------------------------
| Admin & Rules Routes
|--------------------------------------------------------------------------
|
| Routes untuk admin configuration, rules engine, dan audit logs.
| Restricted to admin roles only.
|
*/

Route::middleware(['auth'])->group(function () {
    
    // ===================================================================
    // FREQUENCY RULES (Business Rules Engine)
    // ===================================================================
    
    Route::resource('frequency-rules', FrequencyRulesController::class)
        ->names([
            'index' => 'frequency-rules.index',
            'create' => 'frequency-rules.create',
            'store' => 'frequency-rules.store',
            'show' => 'frequency-rules.show',
            'edit' => 'frequency-rules.edit',
            'update' => 'frequency-rules.update',
            'destroy' => 'frequency-rules.destroy',
        ])
        ->middleware('role:Operator Sekolah,Kepala Sekolah');

    // ===================================================================
    // PEMBINAAN INTERNAL RULES
    // ===================================================================
    
    Route::resource('pembinaan-internal-rules', PembinaanInternalRulesController::class)
        ->names([
            'index' => 'pembinaan-internal-rules.index',
            'create' => 'pembinaan-internal-rules.create',
            'store' => 'pembinaan-internal-rules.store',
            'show' => 'pembinaan-internal-rules.show',
            'edit' => 'pembinaan-internal-rules.edit',
            'update' => 'pembinaan-internal-rules.update',
            'destroy' => 'pembinaan-internal-rules.destroy',
        ])
        ->middleware('role:Operator Sekolah,Kepala Sekolah');

    // ===================================================================
    // RULES ENGINE SETTINGS
    // ===================================================================
    
    Route::prefix('rules-settings')->name('rules-settings.')->middleware('role:Operator Sekolah')->group(function () {
        Route::get('/', [RulesEngineSettingsController::class, 'index'])
            ->name('index');

        Route::put('/', [RulesEngineSettingsController::class, 'update'])
            ->name('update');
    });

    // ===================================================================
    // AUDIT / ACTIVITY LOG
    // ===================================================================
    
    Route::prefix('audit')->name('audit.')->group(function () {
        Route::get('/activity', [ActivityLogController::class, 'index'])
            ->name('activity.index')
            ->middleware('role:Operator Sekolah,Kepala Sekolah');

        Route::get('/activity/{id}', [ActivityLogController::class, 'show'])
            ->name('activity.show')
            ->middleware('role:Operator Sekolah,Kepala Sekolah');

        Route::delete('/activity/{id}', [ActivityLogController::class, 'destroy'])
            ->name('activity.destroy')
            ->middleware('role:Operator Sekolah');

        Route::post('/activity/clear-old', [ActivityLogController::class, 'clearOld'])
            ->name('activity.clear-old')
            ->middleware('role:Operator Sekolah');
    });

    // ===================================================================
    // KEPALA SEKOLAH SPECIFIC ROUTES
    // ===================================================================
    
    Route::prefix('kepala-sekolah')->name('kepala-sekolah.')->middleware('role:Kepala Sekolah,Waka Kesiswaan')->group(function () {
        
        // Approvals
        Route::prefix('approvals')->name('approvals.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Report\ApprovalController::class, 'index'])
                ->name('index');

            Route::get('/history', [\App\Http\Controllers\Report\ApprovalController::class, 'history'])
                ->name('history');

            Route::get('/statistics', [\App\Http\Controllers\Report\ApprovalController::class, 'statistics'])
                ->name('statistics');
        });

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Report\ReportController::class, 'index'])
                ->name('index');

            Route::get('/pelanggaran', [\App\Http\Controllers\Report\ReportController::class, 'pelanggaranReport'])
                ->name('pelanggaran');

            Route::get('/tindak-lanjut', [\App\Http\Controllers\Report\ReportController::class, 'tindakLanjutReport'])
                ->name('tindak-lanjut');
        });

        // Siswa Perlu Pembinaan
        Route::prefix('siswa-perlu-pembinaan')->name('siswa-perlu-pembinaan.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Report\SiswaPerluPembinaanController::class, 'index'])
                ->name('index');

            Route::get('/{siswa}', [\App\Http\Controllers\Report\SiswaPerluPembinaanController::class, 'show'])
                ->name('show');

            Route::get('/export/excel', [\App\Http\Controllers\Report\SiswaPerluPembinaanController::class, 'exportExcel'])
                ->name('export.excel');
        });
    });

    // ===================================================================
    // BACKWARD COMPATIBILITY ALIASES
    // ===================================================================
    
    // data-jurusan → jurusan (alias)
    Route::prefix('data-jurusan')->name('data-jurusan.')->group(function () {
        Route::get('/', function () {
            return redirect()->route('jurusan.index');
        })->name('index');
    });

    // data-kelas → kelas (alias)
    Route::prefix('data-kelas')->name('data-kelas.')->group(function () {
        Route::get('/', function () {
            return redirect()->route('kelas.index');
        })->name('index');
    });

    // my-riwayat → riwayat/my (alias)
    Route::prefix('my-riwayat')->name('my-riwayat.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Pelanggaran\RiwayatPelanggaranController::class, 'myIndex'])
            ->name('index');
    });

    // pelanggaran.create → riwayat.create (alias for menu compatibility)
    Route::get('/pelanggaran/create', function () {
        return redirect()->route('riwayat.create');
    })->name('pelanggaran.create');
});
