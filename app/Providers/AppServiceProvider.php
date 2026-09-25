<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\Shipping\ShippingManager::class, function ($app) {
            return new \App\Services\Shipping\ShippingManager($app);
        });

        $this->app->bind(
            \App\Services\Shipping\Contracts\ShippingCalculatorInterface::class,
            \App\Services\Shipping\ShippingManager::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
