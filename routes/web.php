<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LiturgyController;
use App\Http\Controllers\SongController;
use App\Http\Controllers\LiturgyTemplateController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::prefix('liturgi')->group(function () {
        Route::get('/galeri', [LiturgyController::class, 'gallery'])->name('liturgy.gallery');
        
        // --- CRUD MASTER TEMPLATE (BARU) ---
        Route::prefix('template')->name('liturgy.template.')->group(function () {
            Route::get('/create', [LiturgyTemplateController::class, 'create'])->name('create');
            Route::post('/store', [LiturgyTemplateController::class, 'store'])->name('store');
            Route::delete('/{id}', [LiturgyTemplateController::class, 'destroy'])->name('destroy');
        });

        // --- SISTEM JADWAL & BUILDER ---
        Route::get('/create', [LiturgyController::class, 'create'])->name('liturgy.create');
        Route::post('/store', [LiturgyController::class, 'store'])->name('liturgy.store');
        Route::get('/builder', [LiturgyController::class, 'builder'])->name('liturgy.builder');
        Route::post('/builder/store', [LiturgyController::class, 'storeCustom'])->name('liturgy.store_custom');
        Route::delete('/{schedule}', [LiturgyController::class, 'destroy'])->name('liturgy.destroy');
        Route::get('/kontrol/{schedule}', [LiturgyController::class, 'edit'])->name('liturgy.edit');
        Route::put('/update/{schedule}', [LiturgyController::class, 'update'])->name('liturgy.update');
        Route::get('/output/{schedule}', [LiturgyController::class, 'presentation'])->name('liturgy.presentation');
        Route::get('/export-pdf/{schedule}', [LiturgyController::class, 'exportPdf'])->name('liturgy.export_pdf');
    });

    Route::resource('songs', SongController::class);
    Route::get('/songs/{song}/print', [SongController::class, 'print'])->name('songs.print');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/api/fetch-alkitab', [LiturgyController::class, 'fetchAlkitab'])->name('api.alkitab');
Route::get('/api/fetch-lagu', [SongController::class, 'apiFetch'])->name('api.lagu');

require __DIR__.'/auth.php';