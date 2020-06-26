<?php

namespace App\Providers;

use App\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(\Tymon\JWTAuth\Providers\LumenServiceProvider::class);
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {

        $this->app['auth']->viaRequest('api', function ($request) {
            return app('auth')->setRequest($request)->user();
        });
    }
}
