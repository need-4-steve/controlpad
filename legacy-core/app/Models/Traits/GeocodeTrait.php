<?php

namespace App\Models\Traits;

use App\Repositories\Eloquent\GeocodeRepository;
use App\Models\GeoLocation;
use Auth;
use Request;

trait GeocodeTrait
{
    /*
     * This 'magic method' is called just like it was boot() on a base model.
     */
    public static function bootGeocodeTrait()
    {
        static::created(function ($model) {
            static::saveCode($model);
        });

        static::updated(function ($model) {
            static::saveCode($model);
        });

        static::deleted(function ($model) {
            $model->geoLocation->delete();
        });
    }

    // Function to save latitude and longitude for an address
    protected static function saveCode($address)
    {
        $geo = GeocodeRepository::geocodeStatic($address);

        if (! (is_array($geo) && isset($geo['error']))) {
            $geo = $geo->first();
            $lat = $geo->getLatitude();
            $lng = $geo->getLongitude();
            Geolocation::updateOrCreate(
                [
                    'address_id' => $address->id
                ],
                [
                    'latitude' => $lat,
                    'longitude' => $lng,
                ]
            );
        }
    }

    // Apply geolocation relationship to this model
    public function geoLocation()
    {
        return $this->hasOne(GeoLocation::class);
    }
}
