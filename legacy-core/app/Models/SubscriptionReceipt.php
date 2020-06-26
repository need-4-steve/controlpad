<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionReceipt extends Model
{

    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'subscription_receipts';

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
        'duration',
        'subscription_id',
        'subtotal_price',
        'title',
        'transaction_id',
        'user_id',
        'total_price',
        'total_tax',
        'tax_invoice_pid'
    ];


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }
}
