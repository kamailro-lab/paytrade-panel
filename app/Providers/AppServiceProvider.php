<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Wymuś HTTPS dla wszystkich generowanych linków (assets, route, redirecty)
        // żeby uniknąć mixed-content gdy serwer jest za reverse proxy (letshost.ie)
        // i $_SERVER['HTTPS'] nie jest poprawnie ustawione.
        if (app()->environment('production') || request()->header('x-forwarded-proto') === 'https') {
            URL::forceScheme('https');
        }
    }
}
