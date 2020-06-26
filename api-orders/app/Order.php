<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Tracking;

class Order extends Model
{
    use SoftDeletes;

    public static $rules = [
        'status' => 'required|string|exists:order_status,name'
    ];

    public static $updateRules = [
        'status' => 'sometimes|string|exists:order_status,name'
    ];

    protected $fillable = [
        'pid',
        'receipt_id',
        'confirmation_code',
        'customer_id',
        'buyer_id',
        'buyer_pid',
        'buyer_first_name',
        'buyer_last_name',
        'buyer_email',
        'seller_id',
        'seller_pid',
        'seller_name',
        'store_owner_user_id',
        'inventory_user_id',
        'type_id',
        'transaction_id',
        'gateway_reference_id',
        'total_price',
        'subtotal_price',
        'total_discount',
        'total_tax',
        'total_shipping',
        'tax_invoice_pid',
        'shipping_rate_id',
        'coupon_id',
        'paid_at',
        'status',
        'cash',
        'cash_type',
        'source',
        'deleted_at',
        'comm_engine_status_id',
        'tax_not_charged',
        'lines',
        'payment_type',
        'billing_address',
        'shipping_address',
    ];

    protected $hidden = [
    ];

    protected $appends = [
        'type'
    ];

    protected $casts = [
        'total_price' => 'double',
        'subtotal_price' => 'double',
        'total_discount' => 'double',
        'total_shipping' => 'double',
        'total_tax' => 'double',
        'shipping_is_billing' => 'boolean',
        'cash' => 'boolean'
    ];

    public function payments()
    {
        return $this->hasMany(OrderPayment::class);
    }

    public function tracking()
    {
        return $this->hasMany(Tracking::class)->select(Tracking::$updateFields);
    }

    public function coupon()
    {
        return $this->hasOne(Coupon::class, 'id', 'coupon_id');
    }

    public function lines()
    {
        return $this->hasMany(Orderline::class);
    }

    public function getBillingAddressAttribute()
    {
        return json_decode($this->attributes['billing_address']);
    }

    public function setBillingAddressAttribute($value)
    {
        $this->attributes['billing_address'] = json_encode($value);
    }

    public function getShippingAddressAttribute()
    {
        return json_decode($this->attributes['shipping_address']);
    }

    public function setShippingAddressAttribute($value)
    {
        $this->attributes['shipping_address'] = json_encode($value);
    }

    public function getTypeAttribute()
    {
        switch ($this->type_id) {
            case 1:
                return 'Corporate to Rep';
            case 2:
                return 'Corporate to Customer';
            case 3:
                return 'Rep to Customer';
            case 4:
                return 'Rep to Rep';
            case 5:
                return 'Corporate to Admin';
            case 6:
                return 'Fulfilled by Corporate';
            case 7:
                return 'Mixed';
            case 8:
                return 'Transfer Inventory';
            case 9:
                return 'Affiliate';
            case 10:
                return 'Personal Use';
            case 11:
                return 'Rep Transfer';
            default:
                return 'Unkown';
        }
    }
}
