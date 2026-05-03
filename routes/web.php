<?php

use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\ProgramManagerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DonasiTunaiController;
use App\Http\Controllers\DonaturController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TransaksiController;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/pilih-program', [ProgramController::class, 'index'])->name('pilih.program');
Route::get('/transparansi', function () {
    return Inertia::render('Donasi/Transparansi');
})->name('transparansi');

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {

    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Ganti route lama Anda dengan ini
    Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi');
    Route::put('/donasi/{id}', [TransaksiController::class, 'update'])->name('donasi.update');
    Route::delete('/donasi/{id}', [TransaksiController::class, 'destroy'])->name('donasi.destroy');
    // Route Kelola Pengguna
    Route::get('/pengguna', function () {
        return Inertia::render('Admin/Pengguna', [
            'users' => User::all(), // Mengambil semua user dari tabel users
        ]);
    })->name('pengguna');
    Route::post('/pengguna', [PenggunaController::class, 'store'])->name('pengguna.store');

    // TAMBAHKAN INI:
    // Rute untuk memperbarui data pengguna (menggunakan method PUT)
    Route::put('/pengguna/{id}', [PenggunaController::class, 'update'])->name('pengguna.update');

    // Rute untuk menghapus pengguna (menggunakan method DELETE)
    Route::delete('/pengguna/{id}', [PenggunaController::class, 'destroy'])->name('pengguna.destroy');

    Route::get('/programs', [ProgramManagerController::class, 'index'])->name('programs.index');
    Route::get('/programs/create', [ProgramManagerController::class, 'create'])->name('programs.create');
    Route::post('/programs', [ProgramManagerController::class, 'store'])->name('programs.store');
    Route::post('/programs-update/{id}', [ProgramManagerController::class, 'update_manual'])->name('programs.update_manual');
    Route::delete('/programs/{id}', [ProgramManagerController::class, 'destroy'])->name('programs.destroy');

    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/download/{bulan}', [LaporanController::class, 'download'])->name('laporan.download');

    // Halaman tampil
    Route::get('/pengaturan', [SettingController::class, 'index'])->name('settings.index');

    // Proses simpan
    Route::post('/pengaturan/update', [SettingController::class, 'update'])->name('settings.update');

});

Route::middleware(['auth', 'verified', 'role:staff'])->group(function () {

    Route::get('/staff/dashboard', function () {
        return Inertia::render('Staff/Dashboard');
    })->name('staff.dashboard');

    Route::get('/staff/donasi-tunai', function () {
        return Inertia::render('Staff/InputDonasiTunai', [
            'programs' => Program::select('id', 'judul', 'kategori')->get(),
        ]);
    })->name('donasi-tunai.index');

    // 3. Proses Simpan Donasi Tunai (POST)
    Route::post('/staff/donasi-tunai', [DonasiTunaiController::class, 'store'])
        ->name('donasi-tunai.store');

});

Route::post('/payment/callback', [PaymentController::class, 'callback']);
Route::get('/daftar-donatur', [DonaturController::class, 'index'])->name('donatur.index');

// Letakkan ini di web.php
Route::get('/program/{slug}', [ProgramController::class, 'show'])->name('program.show');

Route::post('/payment/token', [PaymentController::class, 'getSnapToken'])->name('payment.token');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
