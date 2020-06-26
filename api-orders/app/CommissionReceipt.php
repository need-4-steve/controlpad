<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CommissionReceipt extends Model
{

    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'commission_receipts';

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
        'order_id',
        'transaction_id',
        'user_id',
        'amount',
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
