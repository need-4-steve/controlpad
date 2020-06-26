<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sofa\Eloquence\Eloquence;
use App\Models\Media;
use DB;

class Product extends Model
{
    use Eloquence;
    use SoftDeletes;

    public static function boot()
    {
        parent::boot();
        // Prevent Products from being deleted if there is available inventory.
        static::deleting(function ($product) {
            $variants = $product->variants()->pluck('id');
            DB::beginTransaction();
            // Cascade deleting variants. Not possible to do it at database level because of Soft Deletes.
            foreach ($variants as $variant) {
                $delete = Variant::destroy($variant);
                if ($delete == false) {
                    DB::rollBack();
                    return false;
                }
            }
            /**
             * Name and slug are unique on products.
             * Saving the id at the end will make it so the name and slug are reusable.
             * Soft Deletes creates this problem.
             */
            $product->name .= '-'.$product->id;
            $product->slug .= '-'.$product->id;
            $product->save();
            DB::commit();
        });
    }

    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'products';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'long_description',
        'max',
        'min',
        'name',
        'short_description',
        'slug',
        'type_id',
        'user_id',
        'user_pid',
        'tax_class',
        'variant_label',
        'resellable'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public static $selects = [
        'products.id',
        'products.name',
        'slug',
        'short_description',
        'long_description',
        'products.user_id',
        'products.user_pid',
        'type_id',
        'resellable',
        'products.created_at',
        'products.updated_at',
        'products.min',
        'products.max',
        'tax_class',
        'variant_label',
    ];

    /**
     * The rules to apply for validation.
     *
     * @var array
     */
    public static $rules = [
        'categories'        => 'array',
        'categories.*'      => 'array',
        'categories.*.id'   => 'required_with:categories.*|integer|exists:categories,id',
        'images'            => 'array',
        'images.*'          => 'array',
        'images.*.id'       => 'required_with:images.*|integer',
        // Needed for when S3 credentials are figured out within the api.
        // 'images.*.url'      => ['url', 'regex:%\.(gif|jpe?g|png)$%i'],
        'long_description'  => 'max:1020',
        'max'               => 'integer|nullable|min:0',
        'min'               => 'integer|nullable|min:0',
        'name'              => 'required|unique:products',
        'user_id'           => 'required|integer',
        'slug'              => 'required|unique:products|alpha_dash',
        'short_description' => 'max:255',
        'tax_class'         => 'string|max:255',
        'type_id'           => 'sometimes|required|integer|in:1,6',
        'resellable'         => 'sometimes|boolean',
        'variant_label'      => 'string|max:255',
        'visibilities'      => 'array',
        'visibilities.*'    => 'array',
        'visibilities.*.id' => 'required_with:visibilities.*|integer|exists:visibilities,id',
    ];

    protected $searchableColumns = [
        'categories.name'           => 3,
        'items.manufacturer_sku'    => 4,
        'items.size'                => 2,
        'long_description'          => 1,
        'name'                      => 5,
        'short_description'         => 2,
        'items.print'               => 3,
    ];

    protected $casts = [
        'resellable' => 'boolean'
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
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_category');
    }

    public function images()
    {
        return $this->morphToMany(Media::class, 'mediable');
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function variants()
    {
        return $this->hasMany(Variant::class);
    }

    public function visibilities()
    {
        return $this->belongsToMany(Visibility::class);
    }
}
