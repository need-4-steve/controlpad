<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class Variant extends Model
{
    use SoftDeletes;

    public static function boot()
    {
        parent::boot();
        static::deleting(function ($variant) {
            // Prevent Variants from being deleted if there is available inventory.
            $items = $variant->items()->pluck('id');
            // Cascade deleting items. Not possible to do it at database level because of Soft Deletes.
            DB::beginTransaction();
            foreach ($items as $item) {
                $delete = Item::destroy($item);
                if ($delete == false) {
                    DB::rollBack();
                    return false;
                }
            }
            DB::commit();
        });
    }
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'variants';

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
        'max',
        'min',
        'name',
        'option_label',
        'product_id',
        'description'
    ];

    /**
     * The attributes that should be cast to native types
     * E.g. always cast a number as integer instead
     * of a string
     *
     * @var array
     */
    protected $casts = [];

    /**
     * The attributes that should be hidden for arrays
     *
     * @var array
     */
    protected $hidden = [];

    public static $selects = [
        'variants.id',
        'variants.product_id',
        'variants.name',
        'variants.option_label',
        'variants.min',
        'variants.max',
        'variants.description',
        'variants.created_at',
        'variants.updated_at',
        'variants.description'
    ];

    protected $searchableColumns = [
        'categories.name'           => 3,
        'items.manufacturer_sku'    => 4,
        'items.size'                => 2,
        'description'               => 1,
        'name'                      => 5,
        'product.name'              => 4,
    ];

    /**
     * The rules to apply for validation.
     *
     * @var array
     */
    public static $rules = [
        'max'           => 'integer|nullable|min:0',
        'min'           => 'integer|nullable|min:0',
        'option_label'   => 'string|max:255',
        'product_id'    => 'required|integer|exists:products,id',
        'images'            => 'array',
        'images.*'          => 'array',
        'images.*.id'       => 'required_with:images.*|integer',
        'visibilities'      => 'array',
        'visibilities.*'    => 'array',
        'visibilities.*.id' => 'required_with:visibilities.*|integer|exists:visibilities,id',
    ];

    public function getPriceAttribute()
    {
        return money_format("%!n", $this->attributes['price']);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function images()
    {
        return $this->belongsToMany(Media::class);
    }

    public function visibilities()
    {
        return $this->belongsToMany(Visibility::class);
    }
}
