<?php

namespace App\Providers;

use App\Services\AIService;
use App\View\Composers\SidebarComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AIService::class, function ($app) {
            return new AIService;
        });
    }

    public function boot(): void
    {
        if (env('APP_ENV') !== 'local') {
            \URL::forceScheme('https');
        }

        View::composer('layouts.snippets', SidebarComposer::class);
    }
}
