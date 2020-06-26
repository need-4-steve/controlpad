<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'buyer_pid',
        'seller_pid',
        'type',
        'inventory_user_pid',
        'coupon_id',
        'updated_at'
    ];

    protected $hidden = [
        'id'
    ];

    public static $indexParams = [
        'per_page',
        'buyer_pid',
        'seller_pid',
        'type',
        'inventory_user_pid',
        'start_date',
        'end_date'
    ];

    protected $casts = [
        'total_price' => 'double',
        'subtotal_price' => 'double',
        'total_discount' => 'double',
        'total_shipping' => 'double',
        'total_tax' => 'double',
        'shipping_rate_id' => 'integer',
    ];

    public static $createRules = [
        'buyer_pid' => 'nullable|filled|string',// admin or self can be null if anon customer
        'seller_pid' => 'required|string',
        'inventory_user_pid' => 'required|string',
        'type' => 'required|in:retail,wholesale,custom-personal,custom-corp,custom-retail,custom-wholesale,affiliate,custom-affiliate,rep-transfer'
    ];

    public function lines()
    {
        return $this->hasMany(Cartline::class);
    }

    public function coupon()
    {
        return $this->hasOne(Coupon::class, 'id', 'coupon_id');
    }

    public function isEmpty()
    {
        return (count($this->lines) == 0);
    }

    public function calculateSubtotal()
    {
        $subtotal = 0.00;
        if ($this->type === 'custom-personal') {
            return 0.00;
        }
        foreach ($this->lines as $key => $line) {
            $subtotal += ($line->price * $line->quantity);
        }
        return round($subtotal, 2);
    }

    public function getPremiumShipping()
    {
        $premiumShipping = 0.00;
        foreach ($this->lines as $key => $line) {
            if (isset($line->item_id) && isset($line->items[0]->premium_shipping_amount)) { // item_id has one item
                $premiumShipping += $line->items[0]->premium_shipping_amount * $line->quantity;
            } elseif (isset($line->bundle_id)) { // bundle can have lots of items, with nested quantities
                foreach ($line->items as $key => $item) {
                    if (isset($item->premium_shipping_amount)) {
                        $premiumShipping += $item->premium_shipping_amount * $item->quantity * $line->quantity;
                    }
                }
            }
        }
        return round($premiumShipping, 2);
    }

    public function isCustom()
    {
        // Don't include custom-affiliate because seller doesn't have permission to alter the order
        return in_array($this->type, ['custom-corp', 'custom-retail', 'custom-wholesale', 'rep-transfer']);
    }

    public function isWholesale()
    {
        return in_array($this->type, ['wholesale', 'custom-wholesale']);
    }
}
