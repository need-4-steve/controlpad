<?php

namespace App\Models;

use App\Models\Traits\HistoryTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HistoryTrait;
    use SoftDeletes;

    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'invoices';

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
        'customer_id',
        'store_owner_user_id',
        'token',
        'expires_at',
        'subtotal_price',
        'total_shipping',
        'shipping_rate_id',
        'uid',
        'order_id',
        'total_discount',
        'coupon_code',
        'couponable'
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
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function invoiceItems()
    {
        return $this->belongsToMany(Item::class)->withPivot('quantity');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function notes()
    {
        return $this->morphMany(Note::class, 'noteable');
    }

    public function lines()
    {
        return $this->belongsToMany(Item::class)->withPivot('quantity');
    }

    public function orderType()
    {
        return $this->belongsTo(OrderType::class, 'type_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'store_owner_user_id');
    }
}
