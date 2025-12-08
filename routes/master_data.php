<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MasterData\JurusanController;
use App\Http\Controllers\MasterData\KelasController;

/*
|--------------------------------------------------------------------------
| Master Data Routes
|--------------------------------------------------------------------------
|
| Routes untuk manajemen master data (Jurusan, Kelas).
| Restricted to Operator Sekolah only.
|
*/

Route::middleware(['auth'])->group(function () {
    
    // ===================================================================
    // JURUSAN ROUTES
    // ===================================================================
    
    Route::resource('jurusan', JurusanController::class)
        ->names([
            'index' => 'jurusan.index',
            'create' => 'jurusan.create',
            'store' => 'jurusan.store',
            'show' => 'jurusan.show',
            'edit' => 'jurusan.edit',
            'update' => 'jurusan.update',
            'destroy' => 'jurusan.destroy',
        ])
        ->middleware('role:Operator Sekolah');

    // ===================================================================
    // KELAS ROUTES
    // ===================================================================
    
    Route::resource('kelas', KelasController::class)
        ->parameters(['kelas' => 'kelas']) // Force parameter name to be 'kelas' not 'kela'
        ->names([
            'index' => 'kelas.index',
            'create' => 'kelas.create',
            'store' => 'kelas.store',
            'show' => 'kelas.show',
            'edit' => 'kelas.edit',
            'update' => 'kelas.update',
            'destroy' => 'kelas.destroy',
        ])
        ->middleware('role:Operator Sekolah');
});
