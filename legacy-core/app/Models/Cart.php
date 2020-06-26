<?php namespace App\Models;

use Cache;
use Illuminate\Database\Eloquent\Model;
use App\Repositories\Eloquent\InventoryRepository;
use App\Models\Traits\UidTrait;
use App\Models\Traits\EnabledTrait;
use App\Models\Traits\HistoryTrait;

class Cart extends Model
{
    use EnabledTrait, HistoryTrait, UidTrait;

    protected $table = 'carts';

    // Add your validation rules here
    public static $rules = [];

    // Don't forget to fill this array
    protected $fillable = [
       'pid',
       'user_id',
       'buyer_pid',
       'seller_pid',
       'inventory_user_pid',
       'type',
       'total_tax',
       'total_discount',
       'subtotal_price',
       'total_price',
       'total_shipping',
       'shipping_rate_id',
       'tax_invoice_pid'
    ];

    protected $casts = [
        'total_shipping' => 'double'
    ];

    /****************************
    * Relationships
    *****************************/

    public function lines()
    {
        // Filters bundles that are added for new checkout api structure
        return $this->hasMany(Cartline::class)->whereNotNull('item_id');
    }

    public function allLines()
    {
        return $this->hasMany(Cartline::class);
    }

    public function shipping()
    {
        return $this->hasOne(ShippingRate::class, 'id', 'shipping_rate_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bundles()
    {
        return $this->belongsToMany(Bundle::class)->withPivot('quantity', 'id');
    }

    public function coupons()
    {
        return $this->morphToMany(Coupon::class, 'couponable', 'applied_coupons', 'couponable_id', 'coupon_id');
    }

    public function taxInvoice()
    {
        return $this->morphOne(TaxInvoice::class, 'taxable')->latest();
    }
}
