<?php

namespace App\Models;

use App\Models\Traits\EnabledTrait;
use App\Models\Traits\HistoryTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use EnabledTrait;
    use HistoryTrait;
    use SoftDeletes;

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
        'size',
        'print',
        'weight',
        'length',
        'width',
        'height',
        'custom_sku',
        'manufacturer_sku',
        'is_default',
        'location',
        'premium_shipping_cost',
        'wholesale_price',
        'retail_price',
        'premium_price',
    ];

    protected $casts = [
        'weight' => 'float',
        'length' => 'float',
        'width' => 'float',
        'height' => 'float'
    ];

    ################################################################################################
    # Relationships
    ################################################################################################

    public function inventory()
    {
        return $this->hasMany(Inventory::class, 'item_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function prices()
    {
        return $this->morphMany(Price::class, 'priceable');
    }

    public function wholesalePrice()
    {
        return $this->morphOne(Price::class, 'priceable')->where('price_type_id', 1);
    }

    public function msrp()
    {
        return $this->morphOne(Price::class, 'priceable')->where('price_type_id', 2);
    }

    public function premiumPrice()
    {
        return $this->morphOne(Price::class, 'priceable')->where('price_type_id', 3);
    }

    public function cv()
    {
        return $this->morphOne(Price::class, 'priceable')->where('price_type_id', 5);
    }

    public function bundles()
    {
        return $this->belongsToMany(Bundle::class)->withPivot('quantity');
    }

    public function variant()
    {
        return $this->belongsTo(Variant::class, 'variant_id');
    }
}
