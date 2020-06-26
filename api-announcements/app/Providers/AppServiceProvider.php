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
         // register providers
        $this->app->bind(
            \App\Repositories\Interfaces\AnnouncementsRepositoryInterface::class,
            \App\Repositories\EloquentV0\AnnouncementsRepository::class
        );
    }
}
