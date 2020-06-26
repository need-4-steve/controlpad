<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sofa\Eloquence\Eloquence;

class Item extends Model
{
    use Eloquence;
    use SoftDeletes;

    public static function boot()
    {
        parent::boot();
        static::deleting(function ($item) {
            // Prevent Items from being deleted if there is available inventory.
            $inventory = Inventory::where('item_id', $item->id)
                ->sum('inventories.quantity_available');
            if ($inventory > 0) {
                return false;
            }
            // Cascade deleting Inventory. Not possible to do it at database level because of Soft Deletes.
            $inventory = Inventory::where('item_id', $item->id)->pluck('id');
            if (count($inventory) > 0) {
                Inventory::destroy($inventory);
            }
            // Change sku to be null if deleted
            $item->manufacturer_sku = null;
            $item->save();
        });
    }

    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'items';

    /**
     * The attributes that are mass assignable
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'variant_id',
        'size',
        'print',
        'location',
        'manufacturer_sku',
        'premium_shipping_cost',
        'weight',
        'wholesale_price',
        'retail_price',
        'premium_price',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'size',
        'print',
        'manufacturer_sku',
        'length',
        'height',
        'width',
        'custom_sku',
        'priceable_type',
        'priceable_id',
        'price_type_id',
        'is_default',
        'deleted_at',
        'created_at',
        'updated_at',
        'product_id',
        'pivot'
    ];

    protected $casts = [
        'variant_id' => 'integer'
    ];

    public static $rules = [
        'variant_id' => 'required|integer|exists:variants,id',
        'sku' => 'required|string|max:255|unique:items,manufacturer_sku',
        'wholesale_price' => [
            'nullable',
            'numeric',
            'between:0.00,999999.99',
            'regex:/^\d*(\.\d{2})?$/',
            'no_spaces'
        ],
        'retail_price' => [
            'nullable',
            'numeric',
            'between:0.00,999999.99',
            'regex:/^\d*(\.\d{2})?$/',
            'no_spaces'
        ],
        'premium_price' => [
            'nullable',
            'numeric',
            'between:0.00,999999.99',
            'regex:/^\d*(\.\d{2})?$/',
            'no_spaces'
        ],
        'premium_shipping_cost' => [
            'nullable',
            'numeric',
            'between:0.00,999999.99',
            'regex:/^\d*(\.\d{2})?$/',
            'no_spaces'
        ],
        'weight' => ['min:0','nullable','numeric'],
    ];

    protected $searchableColumns = [
        'variant.product.categories.name'       => 3,
        'manufacturer_sku'                      => 4,
        'size'                                  => 2,
        'variant.product.long_description'      => 1,
        'variant.product.name'                  => 5,
        'variant.product.short_description'     => 2,
        'variant.name'                          => 3,
    ];

    public function getWholesalePriceAttribute()
    {
        if (!is_null($this->attributes['wholesale_price'])) {
            return money_format("%!n", $this->attributes['wholesale_price']);
        }
    }

    public function getRetailPriceAttribute()
    {
        if (!is_null($this->attributes['retail_price'])) {
            return money_format("%!n", $this->attributes['retail_price']);
        }
    }

    public function getPremiumPriceAttribute()
    {
        if (!is_null($this->attributes['premium_price'])) {
            return money_format("%!n", $this->attributes['premium_price']);
        }
    }

    public function getInventoryPriceAttribute()
    {
        if (!is_null($this->attributes['inventory_price'])) {
            return money_format("%!n", $this->attributes['inventory_price']);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */
    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }

    public function variant()
    {
        return $this->belongsTo(Variant::class, 'variant_id');
    }
}
