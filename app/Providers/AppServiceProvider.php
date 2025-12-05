<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // // Force HTTPS di production
        // if ($this->app->environment('production')) {
        //     URL::forceScheme('https');
        // }

        // // Force root URL
        // if (config('app.url')) {
        //     URL::forceRootUrl(config('app.url'));
        // }
    }

    public function register(): void
    {
        //
    }
}
