<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Coupon;

class Checkout extends Model
{

    protected $fillable = [
        'pid',
        'cart_pid',
        'buyer_pid',
        'seller_pid',
        'inventory_user_pid',
        'type',
        'total',
        'subtotal',
        'discount',
        'tax',
        'shipping',
        'tax_invoice_pid',
        'shipping_rate_id',
        'billing_address',
        'shipping_address',
        'transfer_pid',
        'lines',
        'coupon_id',
        'tax_exempt',
        'self_pickup'
    ];

    protected $hidden = [
        'id',
        'invoice_id'
    ];

    protected $casts = [
        'total' => 'double',
        'subtotal' => 'double',
        'discount' => 'double',
        'shipping' => 'double',
        'tax' => 'double',
        'shipping_is_billing' => 'boolean',
    ];

    public static $createRules = [
        'billing_address' => 'filled',
        'billing_address.line_1' => 'required_with:billing_address',
        'billing_address.city' => 'required_with:billing_address',
        'billing_address.state' => 'required_with:billing_address',
        'billing_address.zip' => 'required_with:billing_address',
        'billing_address.email' => 'email',
        'shipping_address' => 'filled',
        'shipping_address.line_1' => 'required_with:shipping_address',
        'shipping_address.city' => 'required_with:shipping_address',
        'shipping_address.state' => 'required_with:shipping_address',
        'shipping_address.zip' => 'required_with:shipping_address',
        'discount' => 'numeric|min:0',
        'shipping' => 'numeric|min:0',
        'subtotal' => 'required|numeric|min:0',
        'type' => 'required|in:retail,wholesale,custom-personal,custom-corp,custom-retail,custom-wholesale,affiliate,custom-affiliate,rep-transfer',
        'buyer_pid' => 'required|string',
        'seller_pid' => 'required|string',
        'inventory_user_pid' => 'required|string',
        'coupon_id',
        'lines.*.inventory_owner_pid' => 'required|string',
        'lines.*.price' => 'required|numeric|min:0',
        'lines.*.item_id' => 'required_without:lines.*.bundle_id',
        'lines.*.bundle_id' => 'required_without:lines.*.item_id',
        'lines.*.bundle_name' => 'required_with:lines.*.bundle_id',
        'lines.*.tax_class' => 'filled|nullable|string',
        'lines.*.items' => 'required',
        'lines.*.items.*.quantity' => 'required_with:lines.*.bundle_id',
        'lines.*.items.*.id' => 'required|integer',
        'lines.*.items.*.inventory_id' => 'required|integer',
        'lines.*.items.*.product_name' => 'required|string',
        'lines.*.items.*.variant_name' => 'required|string',
        'lines.*.items.*.option_label' => 'required|string',
        'lines.*.items.*.option' => 'required|string',
        'lines.*.items.*.sku' => 'required|string',
        'lines.*.items.*.premium_shipping_cost' => 'nullable|numeric|min:0',
        'lines.*.items.*.img_url' => 'required|url',
    ];

    public function getBillingAddressAttribute()
    {
        if (isset($this->attributes['billing_address'])) {
            return json_decode($this->attributes['billing_address']);
        } else {
            return null;
        }
    }

    public function setBillingAddressAttribute($value)
    {
        $this->attributes['billing_address'] = (isset($value) ? json_encode($value) : null);
    }

    public function getShippingAddressAttribute()
    {
        if (isset($this->attributes['shipping_address'])) {
            return json_decode($this->attributes['shipping_address']);
        } else {
            return null;
        }
    }

    public function setShippingAddressAttribute($value)
    {
        $this->attributes['shipping_address'] = (isset($value) ? json_encode($value) : null);
    }

    public function getLinesAttribute()
    {
        return json_decode($this->attributes['lines']);
    }

    public function setLinesAttribute($value)
    {
        $this->attributes['lines'] = json_encode($value);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
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

    public function getItemsQuantity()
    {
        $count = 0;
        foreach ($this->lines as $key => $line) {
            if ($line->bundle_id == null) {
                // line is an item
                $count += $line->quantity;
            } elseif ($line->item_id == null) {
                // line is a bundle
                foreach ($line->items as $key => $item) {
                    $count += $item->quantity;
                }
            }
        }
        return $count;
    }

    public function isCustom()
    {
        // Allows custom discount and shipping prices to be set, inventory owner only
        // Don't include custom-affiliate because seller doesn't have permission to alter the order
        return in_array($this->type, ['custom-corp', 'custom-retail', 'custom-wholesale', 'rep-transfer']);
    }

    public function requiresBuyer()
    {
        // These types are creating by the seller and can be assigned to an annonomous buyer
        return in_array($this->type, ['custom-corp', 'custom-retail', 'custom-wholesale', 'custom-affiliate']);
    }

    public function isWholesale()
    {
        return in_array($this->type, ['wholesale', 'custom-wholesale']);
    }

    public function calculateSubtotal()
    {
        $subtotal = 0.00;
        foreach ($this->lines as $key => $line) {
            $subtotal += ($line->price * $line->quantity);
        }
        return round($subtotal, 2);
    }

    public function findLineForPid($pid)
    {
        foreach ($this->lines as $key => $line) {
            if ($line->orderline_pid === $pid) {
                return $line;
            }
        }
        return null;
    }
}
