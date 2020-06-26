<?php

namespace App\Models;

use App\Models\Traits\EnabledTrait;
use App\Models\Traits\HistoryTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sofa\Eloquence\Eloquence;

class Product extends Model
{
    use EnabledTrait;
    use HistoryTrait;
    use SoftDeletes;
    use Eloquence;

    // Add your validation rules here
    public static $rules = [
        'name' => 'required|unique:products',
        'slug' => 'required|unique:products',
        'images' => 'required',
        'type_id' => 'required|integer',
        'items' => 'required',
        'items.*.size' => 'required',
        'items.*.manufacturer_sku' => 'required|unique:items|distinct',
        'items.*.weight' => 'required|numeric',
        'items.*.length' => 'required|numeric',
        'items.*.height' => 'required|numeric',
        'items.*.wholesale_price.price' => 'required|numeric',
        'items.*.msrp.price' => 'required|numeric',
        'items.*.premium_price.price' => 'required|numeric',
        'items.*.is_default' => 'required',
        'items.*.location' => 'sometimes|string|max:15|nullable',
        'min' => 'sometimes|nullable|integer',
        'max' => 'sometimes|nullable|integer'
    ];

    // Don't forget to fill this array
    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'long_description',
        'type_id',
        'min',
        'max',
        'tax_class'
    ];

    protected $casts = [
        'type_id' => 'integer'
    ];

    protected $dates = ['deleted_at'];

    protected $searchableColumns = [
        'name',
        'short_description',
        'long_description',
        'tags.name',
        'category.name'
    ];

    public function scopeUserInventory($query, $userId)
    {
        $query->with(
            'inventory.item.msrp',
            'inventory.item.wholesalePrice',
            'inventory.price'
        )->whereHas('inventory', function ($query) use ($userId) {
            $query->where('user_id', $userId);
            if ($userId !== config('site.apex_user_id')) {
                $query->where('quantity_available', '>', 0);
            }
        })->with(['inventory' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
            if ($userId !== config('site.apex_user_id')) {
                $query->where('quantity_available', '>', 0);
            }
        }])->get();
    }

    ################################################################################################
    # Relationships
    ################################################################################################

    public function tags()
    {
        return $this->morphMany(Tag::class, 'taggable');
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function defaultItem()
    {
        return $this->hasOne(Item::class)->where('is_default', true);
    }

    public function category()
    {
        return $this->belongsToMany(Category::class, 'product_category');
    }

    public function media()
    {
        return $this->morphToMany(Media::class, 'mediable');
    }

    public function roles()
    {
        return $this->belongsToMany(Visibility::class);
    }

    public function type()
    {
        return $this->belongsTo(ProductType::class);
    }

    public function inventory()
    {
        return $this->hasManyThrough(Inventory::class, Item::class);
    }

    public function variants()
    {
        return $this->hasMany(Variant::class);
    }
}
