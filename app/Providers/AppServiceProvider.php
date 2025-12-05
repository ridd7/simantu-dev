<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Force HTTPS untuk production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // JANGAN pakai forceRootUrl untuk Railway!
        // Railway sudah handle ini dengan benar via trusted proxies
    }

    public function register(): void
    {
        //
    }
}
