<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\URL;

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
        // Paksa HTTPS jika diakses via HTTPS / reverse proxy / domain https
        if (
            request()->header('X-Forwarded-Proto') === 'https' ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
            str_starts_with(config('app.url'), 'https://') ||
            env('FORCE_HTTPS', false)
        ) {
            URL::forceScheme('https');
        }

        // Bahasa Indonesia untuk Carbon (tanggal/waktu)
        Carbon::setLocale('id');

        // Gunakan Bootstrap 5 untuk pagination
        Paginator::useBootstrapFive();
    }
}
