<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            \App\Repositories\Interfaces\OrderRepositoryInterface::class,
            \App\Repositories\EloquentV0\OrderRepository::class
        );
        // Services
        $this->app->bind(
            \App\Services\InventoryServiceInterface::class,
            \App\Services\InventoryService::class
        );
        $this->app->bind(
            \App\Services\PaymanServiceInterface::class,
            \App\Services\PaymanService::class
        );
        $this->app->bind(
            \App\Services\SettingsServiceInterface::class,
            \App\Services\SettingsService::class
        );
        $this->app->bind(
            \App\Services\ShippingServiceInterface::class,
            \App\Services\ShippingService::class
        );
        $this->app->bind(
            \App\Services\TaxServiceInterface::class,
            \App\Services\TaxService::class
        );
        $this->app->bind(
            \App\Services\UserServiceInterface::class,
            \App\Services\UserService::class
        );
        $this->app->bind(
            'utils',
            \App\Utils::class
        );
    }
}
