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
        $this->app->singleton('mailer', function ($app) {
            return $app->loadComponent('mail', 'Illuminate\Mail\MailServiceProvider', 'mailer');
        });
        $this->app->bind(
            \App\Repositories\Interfaces\EmailsRepositoryInterface::class,
            \App\Repositories\EloquentV0\EmailsRepository::class
        );
    }
}
