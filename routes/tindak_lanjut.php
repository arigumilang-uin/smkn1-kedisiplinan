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
    // SURAT PANGGILAN ROUTES
    // ===================================================================
    
    Route::prefix('surat-panggilan')->name('surat-panggilan.')->group(function () {
        // Generate/Print surat
        Route::get('/{id}', [TindakLanjutController::class, 'printSurat'])
            ->name('print');

        // Download PDF
        Route::get('/{id}/pdf', [TindakLanjutController::class, 'downloadPdf'])
            ->name('pdf');

        // Send email
        Route::post('/{id}/send-email', [TindakLanjutController::class, 'sendEmail'])
            ->name('send-email');
    });
});
