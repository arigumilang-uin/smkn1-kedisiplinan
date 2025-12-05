<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
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
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PelanggaranController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\JenisPelanggaranController;
use App\Http\Controllers\TindakLanjutController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\JurusanController;

/*
|--------------------------------------------------------------------------
| Web Routes - Disciplinary System (Sistem Kepedulian Sekolah)
|--------------------------------------------------------------------------
|
| Rute-rute utama aplikasi sistem pencatatan pelanggaran siswa.
| Diorganisir berdasarkan functional areas:
|   1. Authentication (login/logout)
|   2. Dashboards (role-based)
|   3. Siswa Management (CRUD + bulk operations)
|   4. Pelanggaran (Recording & Follow-up)
|   5. Master Data & Admin (User, Jenis Pelanggaran, Kelas, Jurusan)
|   6. Audit & Reporting (Kepala Sekolah & Operator)
|   7. Developer Tools (Impersonation - non-production only)
|
*/

// ====================================================================
// 1. AUTHENTICATION (Public Routes)
// ====================================================================
// Tanpa middleware 'auth' - siapa saja bisa akses login

Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// a) Rute yang hanya butuh user login (belum wajib lengkapi profil)
Route::middleware(['auth'])->group(function () {
    Route::get('/profil/lengkapi', [ProfileController::class, 'showCompleteForm'])
        ->name('profile.complete.show');
    Route::post('/profil/lengkapi', [ProfileController::class, 'storeCompleteForm'])
        ->name('profile.complete.store');

    // Halaman "Akun Saya" untuk mengedit email & kontak sendiri
    Route::get('/akun', [ProfileController::class, 'editAccount'])
        ->name('account.edit');
    Route::put('/akun', [ProfileController::class, 'updateAccount'])
        ->name('account.update');

    // Verifikasi email - bersifat opsional (tidak mengunci akses fitur)
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect()->intended('/');
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('status', 'verification-link-sent');
    })->middleware('throttle:6,1')->name('verification.send');
});

// b) Rute yang membutuhkan user login DAN profil sudah lengkap
Route::middleware(['auth', 'profile.completed'])->group(function () {

// ====================================================================
// 2. PROTECTED AREA (Login Required)
// ====================================================================
    // ================================================================
    // A. DASHBOARDS - Role-based Landing Pages
    // ================================================================
    // Setiap role memiliki dashboard sendiri dengan data yang di-scope

    /**
     * Dashboard Admin (Operator Sekolah, Waka Kesiswaan)
     * Menampilkan ringkasan data siswa, pelanggaran, kasus aktif
     */
    Route::get('/dashboard/admin', [AdminDashboardController::class, 'index'])
        ->middleware('role:Operator Sekolah,Waka Kesiswaan')
        ->name('dashboard.admin');

    /**
     * Dashboard Kepala Sekolah
     * Menampilkan kasus pending approval, laporan statistik pelanggaran
     */
    Route::get('/dashboard/kepsek', [KepsekDashboardController::class, 'index'])
        ->middleware('role:Kepala Sekolah')
        ->name('dashboard.kepsek');

    /**
     * Dashboard Kaprodi (Kepala Program Studi)
     * Menampilkan data pelanggaran siswa per jurusan
     */
    Route::get('/dashboard/kaprodi', [KaprodiDashboardController::class, 'index'])
        ->middleware('role:Kaprodi')
        ->name('dashboard.kaprodi');

    /**
     * Dashboard Wali Kelas
     * Menampilkan data pelanggaran siswa di kelas binaan
     */
    Route::get('/dashboard/walikelas', [WaliKelasDashboardController::class, 'index'])
        ->middleware('role:Wali Kelas')
        ->name('dashboard.walikelas');

    /**
     * Dashboard Wali Murid (Orang Tua)
     * Menampilkan riwayat pelanggaran anak yang dibina
     */
    Route::get('/dashboard/wali_murid', [WaliMuridDashboardController::class, 'index'])
        ->middleware('role:Wali Murid')
        ->name('dashboard.wali_murid');

    /**
     * Dashboard Developer (non-production only)
     * Development tools & impersonation status
     */
    Route::get('/dashboard/developer', [\App\Http\Controllers\Dashboard\DeveloperDashboardController::class, 'index'])
        ->name('dashboard.developer');


    // ================================================================
    // B. SISWA MANAGEMENT (CRUD Operations)
    // ================================================================
    // Manajemen data master siswa dengan kontrol akses granular

    /**
     * INDEX - Lihat daftar siswa (read-only)
     * Akses: Admin roles dapat melihat semua siswa
     */
    Route::get('/siswa', [SiswaController::class, 'index'])
        ->middleware('role:Operator Sekolah,Waka Kesiswaan,Wali Kelas,Kaprodi,Kepala Sekolah')
        ->name('siswa.index');

    /**
     * EDIT & UPDATE - Ubah data siswa
     * Akses: Operator (full edit), Wali Kelas (limited edit)
     */
    Route::middleware(['role:Operator Sekolah,Wali Kelas'])->group(function () {
        Route::get('/siswa/{siswa}/edit', [SiswaController::class, 'edit'])->name('siswa.edit');
        Route::put('/siswa/{siswa}', [SiswaController::class, 'update'])->name('siswa.update');
    });

    /**
     * CREATE & DELETE - Tambah/hapus siswa (master data)
     * Akses: HANYA Operator Sekolah (data integrity protection)
     */
    Route::middleware(['role:Operator Sekolah'])->group(function () {
        Route::get('/siswa/create', [SiswaController::class, 'create'])->name('siswa.create');
        Route::post('/siswa', [SiswaController::class, 'store'])->name('siswa.store');
        
        // Bulk import siswa (form + processing + success page)
        Route::get('/siswa/bulk-create', [SiswaController::class, 'bulkCreate'])->name('siswa.bulk.create');
        Route::post('/siswa/bulk-store', [SiswaController::class, 'bulkStore'])->name('siswa.bulk.store');
        Route::get('/siswa/bulk-success', [SiswaController::class, 'bulkSuccess'])->name('siswa.bulk.success');
        
        // Download CSV template dengan wali murid credentials
        Route::get('/siswa/bulk-wali-credentials.csv', [SiswaController::class, 'downloadBulkWaliCsv'])->name('siswa.download-bulk-wali-csv');
        
        // Hapus siswa
        Route::delete('/siswa/{siswa}', [SiswaController::class, 'destroy'])->name('siswa.destroy');
    });


    // ================================================================
    // C. PELANGGARAN OPERATIONS (Recording & Follow-up)
    // ================================================================
    // Pencatatan dan pengelolaan pelanggaran siswa beserta tindak lanjut

    /**
     * CATAT PELANGGARAN - Form dan penyimpanan record pelanggaran baru
     * Akses: Guru, Wali Kelas, Waka Kesiswaan, Kaprodi (staff yang wewenang)
     * Proses: Multi-select siswa & jenis pelanggaran, auto-trigger rules engine
     */
    Route::middleware(['role:Guru,Wali Kelas,Waka Kesiswaan,Kaprodi'])->group(function () {
        Route::get('/pelanggaran/catat', [PelanggaranController::class, 'create'])->name('pelanggaran.create');
        Route::post('/pelanggaran/store', [PelanggaranController::class, 'store'])->name('pelanggaran.store');
    });

    // ====== RIWAYAT SAYA (Per-Pencatat) ======
    // Halaman khusus bagi pencatat untuk melihat / mengelola catatan yang DIA catat sendiri.
    Route::middleware(['role:Guru,Wali Kelas,Waka Kesiswaan,Kaprodi'])->group(function () {
        Route::get('/riwayat/saya', [\App\Http\Controllers\MyRiwayatController::class, 'index'])->name('my-riwayat.index');
        Route::get('/riwayat/saya/{riwayat}/edit', [\App\Http\Controllers\MyRiwayatController::class, 'edit'])->name('my-riwayat.edit');
        Route::put('/riwayat/saya/{riwayat}', [\App\Http\Controllers\MyRiwayatController::class, 'update'])->name('my-riwayat.update');
        Route::delete('/riwayat/saya/{riwayat}', [\App\Http\Controllers\MyRiwayatController::class, 'destroy'])->name('my-riwayat.destroy');
    });

    /**
     * RIWAYAT PELANGGARAN - List dengan filtering & pagination
     * Akses: Semua admin roles (dengan data scoping per role)
     * Filter: Tanggal, jenis pelanggaran, guru pencatat, nama siswa
     */
    Route::get('/riwayat-pelanggaran', [RiwayatController::class, 'index'])
        ->middleware('role:Operator Sekolah,Waka Kesiswaan,Wali Kelas,Kaprodi,Kepala Sekolah')
        ->name('riwayat.index');

    /**
     * FILE SERVICE - Serve bukti foto pelanggaran dari storage
     * Rute ini menghindari 403 ketika public/storage symlink tidak tersedia
     * Akses: User yang terautentikasi saja
     */
    Route::get('/bukti/{path}', [\App\Http\Controllers\FileController::class, 'show'])
        ->where('path', '.*')
        ->name('bukti.show');

    /**
     * KELOLA KASUS TINDAK LANJUT - Update status, tambah denda, cetak surat panggilan
     * Akses: Staff yang berwewenang (Wali Kelas, Waka, Kepala Sekolah, Operator)
     * Business Rules: Proteksi status transitions, audit trail
     */
    Route::middleware(['role:Wali Kelas,Waka Kesiswaan,Kepala Sekolah,Operator Sekolah,Kaprodi'])->group(function () {
        Route::get('/kasus/{id}/kelola', [TindakLanjutController::class, 'edit'])->name('kasus.edit');
        Route::put('/kasus/{id}/update', [TindakLanjutController::class, 'update'])->name('kasus.update');
        Route::get('/kasus/{id}/cetak', [TindakLanjutController::class, 'cetakSurat'])->name('kasus.cetak');
    });


    // ================================================================
    // D. MASTER DATA & ADMIN MANAGEMENT
    // ================================================================
    // Pengelolaan data master sistem (User, Aturan, Struktur Organisasi)

    /**
     * OPERATOR SEKOLAH - Full access ke master data management
     * Includes: Users, Jenis Pelanggaran, Kelas, Jurusan
     * Route Resources: Auto-generated CRUD routes
     */
    Route::middleware(['role:Operator Sekolah'])->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('jenis-pelanggaran', JenisPelanggaranController::class);
        Route::resource('kelas', App\Http\Controllers\KelasController::class)->parameters(['kelas' => 'kelas']);
        Route::resource('jurusan', JurusanController::class)->parameters(['jurusan' => 'jurusan']);
    });


    // ================================================================
    // E. AUDIT, BULK OPERATIONS & ACTIVITY LOGGING
    // ================================================================
    // Operator-only tools untuk data cleanup, bulk delete, audit trail

    Route::middleware(['role:Operator Sekolah'])->prefix('audit')->name('audit.')->group(function () {
        /**
         * SISWA AUDIT - Bulk delete dengan safety checks & confirmation
         * Flow: Show → Preview → Confirm → Execute
         */
        Route::get('/siswa', [\App\Http\Controllers\AuditController::class, 'show'])->name('siswa');
        Route::post('/siswa/preview', [\App\Http\Controllers\AuditController::class, 'preview'])->name('siswa.preview');
        Route::get('/siswa/summary', function() {
            return view('audit.siswa.summary', session()->all());
        })->name('siswa.summary');
        Route::get('/siswa/export', [\App\Http\Controllers\AuditController::class, 'export'])->name('siswa.export');
        Route::get('/siswa/confirm-delete', [\App\Http\Controllers\AuditController::class, 'confirmDelete'])->name('siswa.confirm-delete');
        Route::delete('/siswa', [\App\Http\Controllers\AuditController::class, 'destroy'])->name('siswa.destroy');

        /**
         * ACTIVITY LOG - Audit trail dari semua perubahan data di sistem
         * Tracked: User login, logout, siswa create/update, pelanggaran create, etc
         */
        Route::get('/activity-logs', [\App\Http\Controllers\Dashboard\ActivityLogController::class, 'index'])->name('activity.index');
        Route::get('/activity-logs/{activity}', [\App\Http\Controllers\Dashboard\ActivityLogController::class, 'show'])->name('activity.show');
        Route::get('/activity-logs/export-csv', [\App\Http\Controllers\Dashboard\ActivityLogController::class, 'exportCsv'])->name('activity.export-csv');
    });


    // ================================================================
    // F. KEPALA SEKOLAH MODULES - Approval & Reporting
    // ================================================================
    // Features khusus Kepala Sekolah: persetujuan kasus, laporan, manajemen staff

    Route::middleware(['role:Kepala Sekolah'])->prefix('kepala-sekolah')->name('kepala-sekolah.')->group(function () {
        /**
         * APPROVAL MODULE - Persetujuan surat pemanggilan (terutama Surat 3)
         * Menampilkan kasus yang pending approval, detail siswa, history
         */
        Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
        Route::get('/approvals/{tindakLanjut}', [ApprovalController::class, 'show'])->name('approvals.show');
        Route::put('/approvals/{tindakLanjut}/process', [ApprovalController::class, 'process'])->name('approvals.process');

        /**
         * REPORTS MODULE - Laporan analisis pelanggaran per periode/jurusan
         * Export: CSV, PDF untuk keperluan rapat & dokumentasi
         */
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('/reports/preview', [ReportController::class, 'preview'])->name('reports.preview');
        Route::get('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.export-csv');
        Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf');

        /**
         * USER MANAGEMENT MODULE - Manajemen akun staff (reset password, toggle status)
         * Kepsek bisa monitor status staff & reset password jika lupa
         */
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [UserManagementController::class, 'show'])->name('users.show');
        Route::post('/users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('users.reset-password');
        Route::put('/users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('users.toggle-status');
    });


    // ================================================================
    // G. DEVELOPER TOOLS (Development/Non-Production Only)
    // ================================================================
    // Tools khusus developer untuk testing impersonation & debugging

    /**
     * IMPERSONATION - Set/clear role override di session untuk testing
     * Usage: /developer/impersonate/{role}
     * Non-production feature, disabled di production
     */
    Route::prefix('developer')->group(function () {
        Route::get('/impersonate/{role}', [\App\Http\Controllers\DeveloperController::class, 'impersonate'])->name('developer.impersonate');
        Route::get('/impersonate/clear', [\App\Http\Controllers\DeveloperController::class, 'clear'])->name('developer.impersonate.clear');
        Route::get('/status', [\App\Http\Controllers\DeveloperController::class, 'status'])->name('developer.status');
    });

});
// End of auth middleware group