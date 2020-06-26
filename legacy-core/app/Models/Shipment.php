<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Order;

class Shipment extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'shipments';

    /**
     * The attributes that are mass assignable
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'object_id',
        'object_owner',
        'rate',
        'tracking_number',
        'tracking_url_provider',
        'label_url',
        'parcel',
        'created_at',
        'updated_at',
        'transaction_id',
        'amount',
        'markup',
        'total_price',
        'user_id'
    ];

    /**
     * The attributes that should be hidden for arrays
     *
     * @var array
     */
    protected $hidden = [
        'amount',
        'markup',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */
    public function order()
    {
        return $this->hasOne(Order::class);
    }
}
