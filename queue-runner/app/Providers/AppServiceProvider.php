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
            \App\Services\WebhookServiceInterface::class,
            \App\Services\WebhookService::class
        );
        $this->app->bind(
            \App\Services\NotificationServiceInterface::class,
            \App\Services\CoreNotificationService::class
        );
        $this->app->bind(
            'utils',
            \App\Utils::class
        );
    }
}
