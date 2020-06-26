<?php

namespace CPCommon\RabbitMQ;

use Illuminate\Support\ServiceProvider;

class RabbitServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Nothing
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['rabbit'];
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $manager = $this->app['queue'];

        $manager->addConnector(
            'rabbit',
            function () {
                return new RabbitQueueConnector;
            }
        );
    }
}
