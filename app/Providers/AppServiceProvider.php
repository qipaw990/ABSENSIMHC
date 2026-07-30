<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

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
        // Bahasa Indonesia untuk Carbon (tanggal/waktu)
        Carbon::setLocale('id');

        // Gunakan Bootstrap 5 untuk pagination
        Paginator::useBootstrapFive();
    }
}
