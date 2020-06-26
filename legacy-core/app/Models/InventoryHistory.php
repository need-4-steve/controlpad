<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryHistory extends Model
{

    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'inventory_history';

    /**
     * The attributes that are mass assignable
     *
     * @var array
     */
    protected $fillable = [
        'inventory_id',
        'item_id',
        'inventory_user_id',
        'before_quantity_available',
        'after_quantity_available',
        'before_quantity_staged',
        'after_quantity_staged',
        'auth_user_id',
        'request_email',
        'request_id',
        'request_path',
        'application'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $casts = [];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */
}
