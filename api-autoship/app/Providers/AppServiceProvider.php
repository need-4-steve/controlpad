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
        // \DB::listen(function ($query) {
        //     \Log::debug('sql', [$query]);
        // });
        $this->app->bind(
            \App\Services\Interfaces\V0\AuthServiceInterface::class,
            \App\Services\V0\AuthService::class
        );
        $this->app->bind(
            \App\Services\Interfaces\V0\OrderServiceInterface::class,
            \App\Services\V0\OrderService::class
        );
        $this->app->bind(
            \App\Services\Interfaces\V0\UserServiceInterface::class,
            \App\Services\V0\UserService::class
        );
        $this->app->bind(
            \App\Services\Interfaces\V0\SettingsServiceInterface::class,
            \App\Services\V0\SettingsService::class
        );
        $this->app->bind(
            \App\Services\Interfaces\V0\SubscriptionServiceInterface::class,
            \App\Services\V0\SubscriptionService::class
        );
    }
}
