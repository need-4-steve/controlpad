<?php namespace App\Models;

use Cache;
use App\Models\Traits\HistoryTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Sofa\Eloquence\Eloquence;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;
    use HistoryTrait;
    use Eloquence;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'orders';

    /**
     * The primary key for the model
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pid', 
        'confirmation_code',
        'customer_id',
        'buyer_pid',
        'buyer_email',
        'store_owner_user_id',
        'seller_pid',
        'seller_name',
        'buyer_first_name',
        'buyer_last_name',
        'receipt_id',
        'type_id',
        'total_price',
        'shipping_rate_id',
        'subtotal_price',
        'total_tax',
        'total_shipping',
        'total_discount',
        'transaction_id',
        'coupon_id',
        'paid_at',
        'cash',
        'source',
        'comm_engine_status_id',
        'gateway_reference_id',
        'tax_invoice_pid'
    ];

    protected $searchableColumns = [
        'receipt_id',
        'storeOwner.first_name',
        'storeOwner.last_name',
        'gateway_reference_id',
        'transaction_id'
    ];

    /**
     * The attributes that should be mutated to dates
     * E.g. deleted_at, published_at, etc
     *
     * @var array
     */
    protected $dates = [
        'paid_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Additional attributes
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Validation rules.
     *
     * @var array
     */
    public static $rules = [];

    /**
     * The relations to eager load on every query.
     * Use conservatively.
     *
     * @var array
     */
    protected $with = [];

    /*
    |--------------------------------------------------------------------------
    | Accessors and Mutators
    |--------------------------------------------------------------------------
    |
    | These are methods used to alter existing properties before returning
    | them or to create pseudo-properties for the model.
    |
    */

    /**
     * Count the total number of bundles associated with the order.
     *
     * @return mixed
     */
    public function totalBundles()
    {
        return $this->lines()
            ->where('bundle_id', '!=', null)
            ->where('item_id', null)
            ->count();
    }

    /**
     * Count the total number of products associated with the order.
     *
     * @return mixed
     */
    public function totalProducts()
    {
        return $this->lines()
            ->where('bundle_id', null)
            ->count();
    }

    /*
    |------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    | These are methods that are an alias to more complicated operations
    | to a simple Eloquent method for the model.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */

    public function tracking()
    {
        return $this->hasMany(Tracking::class);
    }

    public function addresses()
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    public function billingAddress()
    {
        return $this->morphOne(Address::class, 'addressable')->where('label', 'Billing');
    }

    public function shippingAddress()
    {
        return $this->morphOne(Address::class, 'addressable')->where('label', 'Shipping');
    }

    public function bundles()
    {
        return $this->lines()
            ->with('bundle', 'bundle.items.product')
            ->where('bundle_id', '!=', null)
            ->where('item_id', null);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id')->withTrashed();
    }

    public function storeOwner()
    {
        return $this->belongsTo(User::class, 'store_owner_user_id');
    }

    public function lines()
    {
        return $this->hasMany(Orderline::class);
    }

    public function sponsor()
    {
        return $this->belongsTo(User::class, 'sponsor_id');
    }

    public function group()
    {
        return $this->belongsToMany(Order::class, 'group_order', 'order_id', 'group_id');
    }

    public function transfers()
    {
        return $this->hasMany(Transfer::class);
    }

    public function salestax()
    {
        return $this->hasMany(SalesTax::class);
    }

    public function shipment()
    {
        return $this->hasOne(Shipment::class);
    }

    public function shipping()
    {
        return $this->hasOne(ShippingRate::class, 'id', 'shipping_rate_id');
    }

    public function taxInvoice()
    {
        return $this->hasOne(TaxInvoice::class, 'taxable_id', 'id');
    }

    public function orderType()
    {
        return $this->belongsTo(OrderType::class, 'type_id');
    }

    public function coupons()
    {
        return $this->morphToMany(Coupon::class, 'couponable', 'applied_coupons', 'couponable_id', 'coupon_id')->withTrashed();
    }

    public function notes()
    {
        return $this->morphMany(Note::class, 'noteable');
    }

    public function returns()
    {
        return $this->hasMany(ReturnModel::class, 'order_id');
    }
}
