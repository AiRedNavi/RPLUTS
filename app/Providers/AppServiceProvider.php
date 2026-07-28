<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
        // Railway (dan reverse proxy lain seperti Heroku/Render) menerima
        // koneksi HTTPS di proxy-nya, tapi meneruskan request ke container
        // sebagai HTTP biasa. Tanpa baris ini, Laravel generate URL asset
        // (asset(), url(), route()) dengan skema http:// meski halaman
        // aslinya dibuka lewat https:// — browser lalu blokir sebagai
        // "Mixed Content".
        if (config('app.env') === 'production' || str(config('app.url'))->startsWith('https')) {
            URL::forceScheme('https');
        }
    }
}