<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LiturgyController;
use App\Http\Controllers\SongController; // <-- Ditambahkan untuk Database Lagu

// Redirect utama ke Galeri (Dashboard)
Route::get('/', function () { 
    return redirect()->route('liturgy.gallery'); 
});

// --- Manajemen Jadwal (Dashboard Utama) ---
Route::get('/liturgi/galeri', [LiturgyController::class, 'gallery'])->name('liturgy.gallery');

// --- Metode Pembuatan 1: Template Baku (Seeder) ---
Route::get('/liturgi/create', [LiturgyController::class, 'create'])->name('liturgy.create');
Route::post('/liturgi/store', [LiturgyController::class, 'store'])->name('liturgy.store');

// --- Metode Pembuatan 2: Builder Kustom (Blank Canvas) ---
Route::get('/liturgi/builder', [LiturgyController::class, 'builder'])->name('liturgy.builder');
Route::post('/liturgi/builder/store', [LiturgyController::class, 'storeCustom'])->name('liturgy.store_custom');

// --- Hapus Jadwal ---
Route::delete('/liturgi/{schedule}', [LiturgyController::class, 'destroy'])->name('liturgy.destroy');

// --- Control Panel (Pusat Kendali Live Editor) ---
Route::get('/liturgi/kontrol/{schedule}', [LiturgyController::class, 'edit'])->name('liturgy.edit');
Route::post('/liturgi/update/{schedule}', [LiturgyController::class, 'update'])->name('liturgy.update');

// --- Output Layar Proyektor ---
Route::get('/liturgi/output/{schedule}', [LiturgyController::class, 'presentation'])->name('liturgy.presentation');

// --- Fitur Export ---
Route::get('/liturgi/export-pdf/{schedule}', [LiturgyController::class, 'exportPdf'])->name('liturgy.export_pdf');

// --- Manajemen Database Lagu (CRUD & Cetak) ---
Route::resource('songs', SongController::class);
Route::get('/songs/{song}/print', [SongController::class, 'print'])->name('songs.print');

// --- API Helper ---
Route::get('/api/fetch-alkitab', [LiturgyController::class, 'fetchAlkitab'])->name('api.alkitab');
Route::get('/api/fetch-lagu', [SongController::class, 'apiFetch'])->name('api.lagu'); // <-- API Lagu

// --- Utility (Refresh Seeder Instan) ---
Route::get('/refresh-seeder-instan', function () {
    \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'LiturgySeeder']);
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    return "SUKSES! Data Liturgi dan Cache berhasil diperbarui.";
});