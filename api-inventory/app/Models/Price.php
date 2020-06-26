<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{

    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'prices';

    /**
     * The attributes that are mass assignable
     *
     * @var array
     */
    protected $fillable = [
        'price',
        'price_type_id',
        'priceable_type',
        'priceable_id',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */
}
