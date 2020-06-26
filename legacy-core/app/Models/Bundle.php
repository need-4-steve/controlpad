<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\EnabledTrait;
use App\Models\Traits\HistoryTrait;
use Sofa\Eloquence\Eloquence;

class Bundle extends Model
{
    use EnabledTrait;
    use HistoryTrait;
    use SoftDeletes;
    use Eloquence;

    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'bundles';

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
        'name',
        'slug',
        'short_description',
        'long_description',
        'starter_kit',
        'tax_class',
        'wholesale_price',
    ];

    public static $rules = [
        'type_id' => 'required',
        'name' => 'required|unique:bundles,name',
        'slug' => 'required|unique:bundles,slug|alpha_dash',
        'short_description' => 'required|max:50',
        'long_description' => 'required',
        'wholesale_price' => 'required|numeric|min:0.01',
        'starter_kit' => 'required|boolean'
    ];

    protected $searchableColumns = [
        'name',
        'short_description',
        'long_description',
        'tags.name',
        'category.name'
    ];

    protected $appends = [
        'default_media'
    ];

    public function getProducts()
    {
        return Product::whereHas('items', function ($query) {
            $query->whereIn('id', $this->items->pluck('id'));
        })->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */
    public function tags()
    {
        return $this->morphMany(Tag::class, 'taggable');
    }

    public function items()
    {
        return $this->belongsToMany(Item::class)->withPivot('quantity', 'id');
    }

    public function category()
    {
        return $this->belongsToMany(Category::class, 'bundle_category');
    }

    public function media()
    {
        return $this->morphToMany(Media::class, 'mediable');
    }

    public function roles()
    {
        return $this->belongsToMany(Visibility::class);
    }

    public function prices()
    {
        return $this->morphMany(Price::class, 'priceable');
    }

    public function carts()
    {
        return $this->belongsToMany(Cart::class)->withPivot('quantity');
    }

    public function wholesalePrice()
    {
        return $this->morphOne(Price::class, 'priceable')->where('price_type_id', 1);
    }

    /*
     |--------------------------------------------------------------------------
     | Accessors and Mutators
     |--------------------------------------------------------------------------
     |
     | These are methods used to alter existing properties before returning
     | them or to create pseudo-properties for the model.
     |
     */
    public function getDefaultMediaAttribute()
    {
        return $this->media()->first();
    }
}
