<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\TrinoService;

class TrinoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(TrinoService::class, function ($app) {
            return new TrinoService();
        });

        $this->app->alias(TrinoService::class, 'trino');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../../config/trino.php' => config_path('trino.php'),
        ], 'trino-config');
    }
}

