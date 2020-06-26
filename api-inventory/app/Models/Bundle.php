<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sofa\Eloquence\Eloquence;
use App\Models\Media;
use DB;

class Bundle extends Model
{
    use Eloquence;
    use SoftDeletes;

    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'bundles';

    /**
     * The attributes that are mass assignable.
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
        'user_id',
        'user_pid',
        'wholesale_price',
    ];

    protected $casts = [
        'wholesale_price' => 'double'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
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
        'items'             => 'array',
        'items.*'           => 'array',
        'items.*.id'        => 'required_with:items.*|integer',
        'items.*.quantity'  => 'required_with:items.*|integer',
        'long_description'  => 'max:2000',
        'name'              => 'required|unique:bundles,name',
        'short_description' => 'max:255',
        'slug'              => 'required|unique:bundles,slug|alpha_dash',
        'starter_kit'       => 'boolean',
        'tax_class'         => 'string|max:255|nullable',
        'type_id'           => 'sometimes|required|integer|in:1',
        'user_id'           => 'required|integer',
        'visibilities'      => 'array',
        'visibilities.*'    => 'array',
        'visibilities.*.id' => 'required_with:visibilities.*|integer|exists:visibilities,id',
        'wholesale_price'   => 'required|numeric|min:0.01',
    ];

    public static $selects = [
        'bundles.id',
        'bundles.name',
        'bundles.slug',
        'bundles.short_description',
        'bundles.long_description',
        'bundles.user_id',
        'bundles.user_pid',
        'bundles.type_id',
        'bundles.created_at',
        'bundles.updated_at',
        'bundles.tax_class',
        'bundles.starter_kit',
        'bundles.wholesale_price',
    ];

    protected $searchableColumns = [
        'categories.name'           => 3,
        'long_description'          => 1,
        'name'                      => 5,
        'short_description'         => 2,
    ];

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
        return $this->belongsToMany(Category::class, 'bundle_category');
    }

    public function images()
    {
        return $this->morphToMany(Media::class, 'mediable');
    }

    public function items()
    {
        return $this->belongsToMany(Item::class)->withPivot('quantity', 'id');
    }

    public function visibilities()
    {
        return $this->belongsToMany(Visibility::class);
    }
}
