<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountType extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'discount_types';

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
        'name',
        'keyname',
        'description'
    ];
}
