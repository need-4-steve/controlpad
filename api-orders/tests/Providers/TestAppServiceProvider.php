<?php namespace Test\Providers;

use Illuminate\Support\ServiceProvider;

class TestAppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Services
        $this->app->bind(
            \App\Services\InventoryServiceInterface::class,
            \Test\MockServices\MockInventoryService::class
        );
        $this->app->bind(
            \App\Services\PaymanServiceInterface::class,
            \Test\MockServices\MockPaymanService::class
        );
        $this->app->bind(
            \App\Services\SettingsServiceInterface::class,
            \Test\MockServices\MockSettingsService::class
        );
        $this->app->bind(
            \App\Services\ShippingServiceInterface::class,
            \Test\MockServices\MockShippingService::class
        );
        $this->app->bind(
            \App\Services\TaxServiceInterface::class,
            \Test\MockServices\MockTaxService::class
        );
        $this->app->bind(
            \App\Services\UserServiceInterface::class,
            \Test\MockServices\MockUserService::class
        );
    }
}
