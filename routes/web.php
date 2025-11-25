<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Dashboard\AdminDashboardController;
use App\Http\Controllers\Dashboard\KepsekDashboardController;
use App\Http\Controllers\Dashboard\KaprodiDashboardController;
use App\Http\Controllers\Dashboard\WaliKelasDashboardController;
use App\Http\Controllers\Dashboard\WaliMuridDashboardController;
use App\Http\Controllers\Dashboard\ApprovalController;
use App\Http\Controllers\Dashboard\ReportController;
use App\Http\Controllers\Dashboard\UserManagementController;
use App\Http\Controllers\Dashboard\ActivityLogController;
use App\Http\Controllers\PelanggaranController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\JenisPelanggaranController;
use App\Http\Controllers\TindakLanjutController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\JurusanController;

/*
|--------------------------------------------------------------------------
| Web Routes (Fixed Roles & Permissions)
|--------------------------------------------------------------------------
*/

// --- 1. AUTHENTICATION ---
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// --- 2. AREA TERPROTEKSI (LOGIN REQUIRED) ---
Route::middleware(['auth'])->group(function () {

    // ====================================================
    // A. DASHBOARD (SESUAI ROLE)
    // ====================================================
    Route::get('/dashboard/admin', [AdminDashboardController::class, 'index'])
        ->middleware('role:Operator Sekolah,Waka Kesiswaan')
        ->name('dashboard.admin');

    Route::get('/dashboard/kepsek', [KepsekDashboardController::class, 'index'])
        ->middleware('role:Kepala Sekolah')
        ->name('dashboard.kepsek');

    Route::get('/dashboard/kaprodi', [KaprodiDashboardController::class, 'index'])
        ->middleware('role:Kaprodi')
        ->name('dashboard.kaprodi');

    Route::get('/dashboard/walikelas', [WaliKelasDashboardController::class, 'index'])
        ->middleware('role:Wali Kelas')
        ->name('dashboard.walikelas');

    Route::get('/dashboard/wali_murid', [WaliMuridDashboardController::class, 'index'])
        ->middleware('role:Wali Murid')
        ->name('dashboard.wali_murid');


    // ====================================================
    // B. MANAJEMEN DATA SISWA (LOGIKA HAK AKSES KETAT)
    // ====================================================
    
    // 1. LIHAT DAFTAR SISWA (READ ONLY)
    // Semua role akademik boleh melihat daftar siswa untuk keperluan monitoring/pencarian
    Route::get('/siswa', [SiswaController::class, 'index'])
        ->middleware('role:Operator Sekolah,Waka Kesiswaan,Wali Kelas,Kaprodi,Kepala Sekolah')
        ->name('siswa.index');

    // 2. EDIT SISWA (UPDATE)
    // Hanya Operator (Full Edit) dan Wali Kelas (Edit Kontak)
    // Waka Kesiswaan TIDAK dimasukkan disini untuk menjaga integritas data
    Route::middleware(['role:Operator Sekolah,Wali Kelas'])->group(function () {
        Route::get('/siswa/{siswa}/edit', [SiswaController::class, 'edit'])->name('siswa.edit');
        Route::put('/siswa/{siswa}', [SiswaController::class, 'update'])->name('siswa.update');
    });

    // 3. TAMBAH & HAPUS SISWA (CREATE & DELETE)
    // HANYA OPERATOR SEKOLAH yang berhak menambah/menghapus siswa (Master Data)
    Route::middleware(['role:Operator Sekolah'])->group(function () {
        Route::get('/siswa/create', [SiswaController::class, 'create'])->name('siswa.create');
        Route::post('/siswa', [SiswaController::class, 'store'])->name('siswa.store');
        // Bulk create siswa (form + processing)
        Route::get('/siswa/bulk-create', [SiswaController::class, 'bulkCreate'])->name('siswa.bulk.create');
        Route::post('/siswa/bulk-store', [SiswaController::class, 'bulkStore'])->name('siswa.bulk.store');
        Route::get('/siswa/bulk-success', [SiswaController::class, 'bulkSuccess'])->name('siswa.bulk.success');
        Route::get('/siswa/bulk-wali-credentials.csv', [SiswaController::class, 'downloadBulkWaliCsv'])->name('siswa.download-bulk-wali-csv');
        Route::delete('/siswa/{siswa}', [SiswaController::class, 'destroy'])->name('siswa.destroy');
    });


    // ====================================================
    // C. OPERASIONAL PELANGGARAN
    // ====================================================

    // 1. CATAT PELANGGARAN (CREATE)
    // Waka Kesiswaan PUNYA akses di sini (Tugas Utama)
    Route::middleware(['role:Guru,Wali Kelas,Waka Kesiswaan,Kaprodi'])->group(function () {
        Route::get('/pelanggaran/catat', [PelanggaranController::class, 'create'])->name('pelanggaran.create');
        Route::post('/pelanggaran/store', [PelanggaranController::class, 'store'])->name('pelanggaran.store');
    });

    // 2. LIHAT RIWAYAT (READ)
    Route::get('/riwayat-pelanggaran', [RiwayatController::class, 'index'])
        ->middleware('role:Operator Sekolah,Waka Kesiswaan,Wali Kelas,Kaprodi,Kepala Sekolah')
        ->name('riwayat.index');

    // 3. KELOLA KASUS / TINDAK LANJUT (UPDATE)
    Route::middleware(['role:Wali Kelas,Waka Kesiswaan,Kepala Sekolah,Operator Sekolah,Kaprodi'])->group(function () {
        Route::get('/kasus/{id}/kelola', [TindakLanjutController::class, 'edit'])->name('kasus.edit');
        Route::put('/kasus/{id}/update', [TindakLanjutController::class, 'update'])->name('kasus.update');
        Route::get('/kasus/{id}/cetak', [TindakLanjutController::class, 'cetakSurat'])->name('kasus.cetak');
    });


    // ====================================================
    // D. MANAJEMEN USER & MASTER DATA (ADMIN ONLY)
    // ====================================================
    // Hanya Operator Sekolah yang boleh mengelola User dan Aturan
    Route::middleware(['role:Operator Sekolah'])->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('jenis-pelanggaran', JenisPelanggaranController::class);
        Route::resource('kelas', App\Http\Controllers\KelasController::class)->parameters(['kelas' => 'kelas']);
        Route::resource('jurusan', JurusanController::class)->parameters(['jurusan' => 'jurusan']);
    });

    // ====================================================
    // E. AUDIT & BULK DELETE (ADMIN ONLY)
    // ====================================================
    Route::middleware(['role:Operator Sekolah'])->prefix('audit')->name('audit.')->group(function () {
        Route::get('/siswa', [\App\Http\Controllers\AuditController::class, 'show'])->name('siswa');
        Route::post('/siswa/preview', [\App\Http\Controllers\AuditController::class, 'preview'])->name('siswa.preview');
        Route::get('/siswa/summary', function() {
            return view('audit.siswa.summary', session()->all());
        })->name('siswa.summary');
        Route::get('/siswa/export', [\App\Http\Controllers\AuditController::class, 'export'])->name('siswa.export');
        Route::get('/siswa/confirm-delete', [\App\Http\Controllers\AuditController::class, 'confirmDelete'])->name('siswa.confirm-delete');
        Route::delete('/siswa', [\App\Http\Controllers\AuditController::class, 'destroy'])->name('siswa.destroy');
    });

    // ====================================================
    // F. KEPALA SEKOLAH - PERSETUJUAN & VALIDASI KASUS
    // ====================================================
    Route::middleware(['role:Kepala Sekolah'])->prefix('kepala-sekolah')->name('kepala-sekolah.')->group(function () {
        // Approval Module
        Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
        Route::get('/approvals/{tindakLanjut}', [ApprovalController::class, 'show'])->name('approvals.show');
        Route::put('/approvals/{tindakLanjut}/process', [ApprovalController::class, 'process'])->name('approvals.process');

        // Reports Module
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('/reports/preview', [ReportController::class, 'preview'])->name('reports.preview');
        Route::get('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.export-csv');
        Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf');

        // User Management Module
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [UserManagementController::class, 'show'])->name('users.show');
        Route::post('/users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('users.reset-password');
        Route::put('/users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('users.toggle-status');

        // Activity Log Module
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity.index');
        Route::get('/activity-logs/{activity}', [ActivityLogController::class, 'show'])->name('activity.show');
        Route::get('/activity-logs/export-csv', [ActivityLogController::class, 'exportCsv'])->name('activity.export-csv');
    });
});