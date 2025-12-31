<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register AI Service as singleton
        $this->app->singleton(\App\Services\AIService::class, function ($app) {
            return new \App\Services\AIService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (env('APP_ENV') !== 'local') {
            \URL::forceScheme('https');
        }
    }
}
