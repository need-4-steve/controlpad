<?php

namespace App\Models\Traits;

use Cache;
use Config;
use App\Models\Setting;

trait EnabledTrait
{
    public static function checkEnabled($model)
    {
        if (Cache::get('settings.enable_' . strtolower(class_basename($model)))) {
            return false;
        }
    }

    /*
     * This 'magic method' is called just like it was boot() on a base model.
     */
    public static function bootEnabledTrait()
    {
        static::creating(function ($model) {
            static::checkEnabled($model);
        });

        static::updating(function ($model) {
            static::checkEnabled($model);
        });

        static::saving(function ($model) {
            static::checkEnabled($model);
        });

        static::deleting(function ($model) {
            static::checkEnabled($model);
        });
    }
}
