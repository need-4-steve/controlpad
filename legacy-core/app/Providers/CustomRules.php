<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Validator;

class CustomRules extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('no_underscores', function ($attribute, $value, $parameters, $validator) {
            if (strpos($value, '_')) {
                return false;
            }
            return true;
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
