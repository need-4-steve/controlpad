<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParcelTemplate extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'parcel_templates';


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
        'user_id',
        'carrier_id',
        'token',
        'name',
        'length',
        'width',
        'height',
        'distance_unit'
    ];

    public static $rules = [];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */
    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }
}
