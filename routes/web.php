<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Dashboard\AdminDashboardController;
use App\Http\Controllers\Dashboard\KepsekDashboardController;
use App\Http\Controllers\Dashboard\KaprodiDashboardController;
use App\Http\Controllers\Dashboard\WaliKelasDashboardController;
use App\Http\Controllers\Dashboard\OrtuDashboardController;
use App\Http\Controllers\PelanggaranController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\JenisPelanggaranController;
use App\Http\Controllers\TindakLanjutController;
use App\Http\Controllers\RiwayatController;

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

    Route::get('/dashboard/ortu', [OrtuDashboardController::class, 'index'])
        ->middleware('role:Orang Tua')
        ->name('dashboard.ortu');


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
    });

});