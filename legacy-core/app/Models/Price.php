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
        'priceable_id'
    ];

    protected $casts = [
        'price_type_id' => 'integer',
        'price' => 'double'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */
    public function priceable()
    {
        return $this->morphTo();
    }

    public function type()
    {
        return $this->hasOne(PriceType::class, 'id', 'price_type_id');
    }
}
