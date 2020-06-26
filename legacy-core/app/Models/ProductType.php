<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductType extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'product_type';

    /**
     * The attributes that are mass assignable
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */
    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
}
