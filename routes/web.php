<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LiturgyController;
use App\Http\Controllers\SongController;
use App\Http\Controllers\LiturgyTemplateController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\PresenterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ScheduleToolsController;
use App\Http\Controllers\ActivityController;

// Aplikasi internal: halaman depan langsung ke dashboard (tamu diarahkan ke login).
Route::redirect('/', '/dashboard');

// Cek kesehatan untuk pemantauan server / load balancer (tanpa sesi, tanpa database).
Route::get('/up', [DashboardController::class, 'health'])->name('health')->withoutMiddleware('web');

Route::middleware(['auth', 'verified'])->group(function () {
    
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // --- PENGATURAN (logo & identitas gereja) ---
    Route::get('/pengaturan', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::post('/pengaturan', [SettingsController::class, 'update'])->name('settings.update');

    // --- PENGGUNA (akun operator; pendaftaran umum ditutup) ---
    Route::resource('pengguna', UserController::class)->except(['show', 'create'])
        ->parameters(['pengguna' => 'user'])->names('users');

    Route::prefix('liturgi')->group(function () {
        Route::get('/galeri', [LiturgyController::class, 'gallery'])->name('liturgy.gallery');
        
        // --- CRUD MASTER TEMPLATE ---
        Route::prefix('template')->name('liturgy.template.')->group(function () {
            Route::get('/create', [LiturgyTemplateController::class, 'create'])->name('create');
            Route::post('/store', [LiturgyTemplateController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [LiturgyTemplateController::class, 'edit'])->name('edit');
            Route::post('/{id}/update', [LiturgyTemplateController::class, 'update'])->name('update');
            Route::delete('/{id}', [LiturgyTemplateController::class, 'destroy'])->name('destroy');
        });

        // --- SISTEM JADWAL & BUILDER ---
        // Buat jadwal dari teks/PDF buletin & duplikat jadwal
        Route::get('/impor', [ScheduleToolsController::class, 'importForm'])->name('liturgy.import');
        Route::post('/impor/periksa', [ScheduleToolsController::class, 'importReview'])->name('liturgy.import.review');
        Route::post('/impor/simpan', [ScheduleToolsController::class, 'importStore'])->name('liturgy.import.store');
        Route::post('/{schedule}/duplikat', [ScheduleToolsController::class, 'duplicate'])->name('liturgy.duplicate');
        Route::get('/{schedule}/buletin', [ScheduleToolsController::class, 'bulletin'])->name('liturgy.bulletin');

        Route::get('/create', [LiturgyController::class, 'create'])->name('liturgy.create');
        Route::post('/store', [LiturgyController::class, 'store'])->name('liturgy.store');
        Route::get('/builder', [LiturgyController::class, 'builder'])->name('liturgy.builder');
        Route::post('/builder/store', [LiturgyController::class, 'storeCustom'])->name('liturgy.store_custom');
        Route::delete('/{schedule}', [LiturgyController::class, 'destroy'])->name('liturgy.destroy');
        
        // --- PEMISAHAN EDIT & KONTROL PANEL ---
        Route::get('/edit/{schedule}', [LiturgyController::class, 'edit'])->name('liturgy.edit');
        Route::get('/kontrol/{schedule}', [LiturgyController::class, 'controlPanel'])->name('liturgy.control');
        Route::post('/update/{schedule}', [LiturgyController::class, 'update'])->name('liturgy.update');
        
        Route::get('/output/{schedule}', [LiturgyController::class, 'presentation'])->name('liturgy.presentation');
        Route::get('/export-pdf/{schedule}', [LiturgyController::class, 'exportPdf'])->name('liturgy.export_pdf');

        // --- DATA LIVE (JSON) & SINKRON 2 KOMPUTER ---
        Route::get('/kontrol/{schedule}/slides', [PresenterController::class, 'slides'])->name('presenter.slides');
        Route::post('/kontrol/{schedule}/settings', [PresenterController::class, 'saveSettings'])->name('presenter.settings');
        Route::get('/live/{schedule}/state', [PresenterController::class, 'state'])->name('presenter.state');
        Route::post('/live/{schedule}/state', [PresenterController::class, 'pushState'])->name('presenter.state.push');
        Route::get('/remote/{schedule}', [PresenterController::class, 'remote'])->name('presenter.remote');
        Route::post('/kontrol/{schedule}/sisip-lagu', [PresenterController::class, 'insertSong'])->name('presenter.insert_song');

        // --- CRUD WARTA SINODE ---
        Route::get('/warta', [AnnouncementController::class, 'index'])->name('announcement.index');
        Route::post('/warta/store', [AnnouncementController::class, 'store'])->name('announcement.store');
        Route::delete('/warta/{id}', [AnnouncementController::class, 'destroy'])->name('announcement.destroy');
    });

    Route::get('/riwayat', [ActivityController::class, 'index'])->name('activity.index');

    Route::resource('songs', SongController::class);
    Route::get('/songs/{song}/print', [SongController::class, 'print'])->name('songs.print');

    // API pencarian lagu & ayat (hanya untuk pengguna yang login)
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('/api/fetch-alkitab', [LiturgyController::class, 'fetchAlkitab'])->name('api.alkitab');
        Route::get('/api/fetch-lagu', [SongController::class, 'apiFetch'])->name('api.lagu');
        Route::get('/api/daftar-lagu', [SongController::class, 'apiList'])->name('api.daftar_lagu');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';