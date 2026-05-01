<?php

use App\Http\Controllers\Admin\ProgramManagerController;
use App\Http\Controllers\DonaturController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgramController;
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

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {

    Route::get('/admin/dashboard', function () {
        return Inertia::render('Admin/Dashboard');
    })->name('admin.dashboard');

    Route::get('/transaksi', function () {
        return Inertia::render('Admin/Transaksi');
    })->name('transaksi');
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

});

Route::middleware(['auth', 'verified', 'role:staff'])->group(function () {

    Route::get('/staff/dashboard', function () {
        return Inertia::render('Staff/Dashboard');
    })->name('staff.dashboard');

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
