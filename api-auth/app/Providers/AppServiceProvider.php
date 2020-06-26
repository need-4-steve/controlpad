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
            \App\Repositories\Interfaces\APIKeyInterface::class,
            \App\Repositories\Eloquent\V0\ApiKeyRepository::class
        );
        $this->app->bind(
            \App\Repositories\Interfaces\TenantInterface::class,
            \App\Repositories\Eloquent\V0\TenantRepository::class
        );
        $this->app->bind(
            \App\Repositories\Interfaces\ServiceInterface::class,
            \App\Repositories\Eloquent\V0\ServiceRepository::class
        );
        $this->app->bind(
            \App\Repositories\Interfaces\UserInterface::class,
            \App\Repositories\Eloquent\V0\UserRepository::class
        );
    }
}
