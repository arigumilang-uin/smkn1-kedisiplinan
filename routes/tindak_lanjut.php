<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TindakLanjut\TindakLanjutController;

/*
|--------------------------------------------------------------------------
| Tindak Lanjut Routes
|--------------------------------------------------------------------------
|
| Routes untuk manajemen Tindak Lanjut (Follow-up Actions).
| Includes approval workflow routes.
|
*/

Route::middleware(['auth'])->group(function () {
    
    // ===================================================================
    // TINDAK LANJUT CRUD ROUTES
    // ===================================================================
    
    Route::resource('tindak-lanjut', TindakLanjutController::class)
        ->names([
            'index' => 'tindak-lanjut.index',
            'create' => 'tindak-lanjut.create',
            'store' => 'tindak-lanjut.store',
            'show' => 'tindak-lanjut.show',
            'edit' => 'tindak-lanjut.edit',
            'update' => 'tindak-lanjut.update',
            'destroy' => 'tindak-lanjut.destroy',
        ]);

    // ===================================================================
    // APPROVAL WORKFLOW ROUTES
    // ===================================================================
    
    Route::prefix('tindak-lanjut')->name('tindak-lanjut.')->group(function () {
        // Approve tindak lanjut
        Route::post('/{id}/approve', [TindakLanjutController::class, 'approve'])
            ->name('approve')
            ->middleware('can:approve,App\Models\TindakLanjut');

        // Reject tindak lanjut
        Route::post('/{id}/reject', [TindakLanjutController::class, 'reject'])
            ->name('reject')
            ->middleware('can:reject,App\Models\TindakLanjut');

        // Complete/close tindak lanjut
        Route::post('/{id}/complete', [TindakLanjutController::class, 'complete'])
            ->name('complete')
            ->middleware('can:complete,App\Models\TindakLanjut');

        // List pending approval (for approvers)
        Route::get('/pending-approval', [TindakLanjutController::class, 'pendingApproval'])
            ->name('pending-approval')
            ->middleware('role:Kepala Sekolah,Waka Kesiswaan,Kaprodi');

        // My approvals (yang saya setujui/tolak)
        Route::get('/my-approvals', [TindakLanjutController::class, 'myApprovals'])
            ->name('my-approvals');

        // Statistics
        Route::get('/statistics', [TindakLanjutController::class, 'statistics'])
            ->name('statistics');
    });

    // ===================================================================
    // SURAT PANGGILAN MANAGEMENT ROUTES
    // ===================================================================
    
    Route::prefix('tindak-lanjut/{id}')->name('tindak-lanjut.')->group(function () {
        // Preview surat (modal/page)
        Route::get('/preview-surat', [TindakLanjutController::class, 'previewSurat'])
            ->name('preview-surat');
        
        // Edit surat content
        Route::get('/edit-surat', [TindakLanjutController::class, 'editSurat'])
            ->name('edit-surat');
        
        // Update surat content
        Route::put('/update-surat', [TindakLanjutController::class, 'updateSurat'])
            ->name('update-surat');
        
        // Cetak surat (Download PDF + Log print activity)
        Route::get('/cetak-surat', [TindakLanjutController::class, 'cetakSurat'])
            ->name('cetak-surat');
        
        // Mulai Tangani (Change status: Baru -> Sedang Ditangani)
        Route::put('/mulai-tangani', [TindakLanjutController::class, 'mulaiTangani'])
            ->name('mulai-tangani');
        
        // Selesaikan Kasus (Change status: Ditangani -> Selesai)
        Route::put('/selesaikan', [TindakLanjutController::class, 'selesaikan'])
            ->name('selesaikan');
    });
});
