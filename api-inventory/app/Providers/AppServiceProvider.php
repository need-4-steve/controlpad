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
            \App\Repositories\Interfaces\ProductInterface::class,
            \App\Repositories\EloquentV0\ProductRepository::class
        );
        $this->app->bind(
            \App\Repositories\Interfaces\ItemInterface::class,
            \App\Repositories\EloquentV0\ItemRepository::class
        );
        $this->app->bind(
            \App\Repositories\Interfaces\CategoryInterface::class,
            \App\Repositories\EloquentV0\CategoryRepository::class
        );
        $this->app->bind(
            \App\Services\Upload\UploadInterface::class,
            \App\Services\Upload\UploadAmazonS3::class
        );
        $this->app->bind(
            \Intervention\Image\Facades\Image::class,
            \Intervention\Image\ImageServiceProvider::class
        );
        $this->app->bind(
            \App\Repositories\Interfaces\ReservationInterface::class,
            \App\Repositories\EloquentV0\ReservationRepository::class
        );
    }
}
