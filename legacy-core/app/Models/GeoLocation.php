<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeoLocation extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'geo_locations';

    /**
     * The primary key for the model
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable
     *
     * @var array
     */
    protected $fillable = [
        'address_id',
        'latitude',
        'longitude'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */
    public function address()
    {
        return $this->belongsTo(Address::class);
    }
}
