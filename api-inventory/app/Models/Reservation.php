<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{

    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'reservations';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable
     *
     * @var array
     */
    protected $fillable = [
        'reservation_id',
        'inventory_id',
        'quantity',
        'expires_at'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];

    protected $casts = [
        'quantity_available' => 'integer',
        'item_id' => 'integer',
    ];

    public static $createRules = [
        'inventories' => 'filled|required_without:transactions|array',
        'inventories.*.transaction_id' => 'string|max:36',
        'inventories.*.id' => 'required_with:inventories|integer',
        'inventories.*.quantity' => 'required_with:inventories|integer|min:1',
        'transactions' => 'filled|required_without:inventories|array',
        'transactions.*.transaction_id' => 'required_with:transactions|string|max:36',
        'transactions.*.inventories' => 'filled|required_with:transactions|array',
        'transactions.*.inventories.*.id' => 'required_with:transactions|integer',
        'transactions.*.inventories.*.quantity' => 'required_with:transactions|integer|min:1'
    ];


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */
}
