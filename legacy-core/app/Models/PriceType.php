<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceType extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'price_types';

    /**
     * The attributes that are mass assignable
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];
}
