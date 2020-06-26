<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Eloquent\StoreSettingRepository;
use Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        view()->composer('layouts.store', function ($view) {
            if ($rep = session('store_owner')) {
                $store_user_id = $rep->id;
            } else {
                $store_user_id = config('site.apex_user_id');
            }
            $settingRepo= new StoreSettingRepository();
            $store_settings = $settingRepo->getSettingsByUser($store_user_id);
            $store_settings['categories'] = $settingRepo->getCategories($store_user_id);
            $view->with('store_settings', $store_settings);
        });

        Validator::extend('alpha_spaces', function ($attribute, $value) {
            return preg_match('/^[\pL\s]+$/u', $value);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (!empty(env('COMMISSION_ENGINE')) && strtolower(env('COMMISSION_ENGINE'))==='mcom'){
            $this->app->bind(\App\Services\Commission\CommissionServiceInterface::class, function () {
                return new \App\Http\Controllers\Api\V2\MCommServices\MCommCommissionService();
            });
        }
    }
}
