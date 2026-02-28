<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LiturgyController;
use App\Http\Controllers\SongController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome'); // Ini halaman depan bawaan Laravel
});

/*
|--------------------------------------------------------------------------
| Protected Routes (Hanya bisa diakses setelah Login)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    
    // --- DASHBOARD UTAMA ---
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // --- APLIKASI MULTIMEDIA (DIPINDAH KE SINI) ---
    Route::prefix('liturgi')->group(function () {
        Route::get('/galeri', [LiturgyController::class, 'gallery'])->name('liturgy.gallery');
        Route::get('/create', [LiturgyController::class, 'create'])->name('liturgy.create');
        Route::post('/store', [LiturgyController::class, 'store'])->name('liturgy.store');
        Route::get('/builder', [LiturgyController::class, 'builder'])->name('liturgy.builder');
        Route::post('/builder/store', [LiturgyController::class, 'storeCustom'])->name('liturgy.store_custom');
        Route::delete('/{schedule}', [LiturgyController::class, 'destroy'])->name('liturgy.destroy');
        Route::get('/kontrol/{schedule}', [LiturgyController::class, 'edit'])->name('liturgy.edit');
        Route::post('/update/{schedule}', [LiturgyController::class, 'update'])->name('liturgy.update');
        Route::get('/output/{schedule}', [LiturgyController::class, 'presentation'])->name('liturgy.presentation');
        Route::get('/export-pdf/{schedule}', [LiturgyController::class, 'exportPdf'])->name('liturgy.export_pdf');
    });

    // --- MANAJEMEN DATABASE LAGU ---
    Route::resource('songs', SongController::class);
    Route::get('/songs/{song}/print', [SongController::class, 'print'])->name('songs.print');

    // --- PENGATURAN PROFIL BREEZE ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// --- API Helper (Biarkan di luar auth jika ingin bisa diakses sistem luar, atau masukkan ke auth jika ingin sangat aman) ---
Route::get('/api/fetch-alkitab', [LiturgyController::class, 'fetchAlkitab'])->name('api.alkitab');
Route::get('/api/fetch-lagu', [SongController::class, 'apiFetch'])->name('api.lagu');

// Rute bawaan Breeze untuk Login/Register
require __DIR__.'/auth.php';