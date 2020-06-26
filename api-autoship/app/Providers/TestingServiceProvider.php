<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class TestingServiceProvider extends ServiceProvider
{


    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            \App\Services\Interfaces\V0\AuthServiceInterface::class,
            \App\Services\Testing\V0\AuthService::class
        );
        $this->app->bind(
            \App\Services\Interfaces\V0\OrderServiceInterface::class,
            \App\Services\Testing\V0\OrderService::class
        );
        $this->app->bind(
            \App\Services\Interfaces\V0\UserServiceInterface::class,
            \App\Services\Testing\V0\UserService::class
        );
        $this->app->bind(
            \App\Services\Interfaces\V0\SettingsServiceInterface::class,
            \App\Services\Testing\V0\SettingsService::class
        );
        $this->app->bind(
            \App\Services\Interfaces\V0\SubscriptionServiceInterface::class,
            \App\Services\V0\SubscriptionService::class
        );
    }
}
