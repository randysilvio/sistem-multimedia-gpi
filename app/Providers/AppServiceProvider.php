<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Semua halaman memakai Bootstrap 5: pagination bawaan Laravel berbasis Tailwind
        Paginator::useBootstrapFive();

        // Di server online di balik HTTPS (termasuk proxy/Cloudflare): semua tautan ikut https
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // Aturan kata sandi seragam untuk akun baru & ganti sandi
        Password::defaults(fn () => Password::min(8));
    }
}
