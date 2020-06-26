<?php

namespace App\Models;

use Cache;
use Config;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HistoryTrait;
use App\Models\Product;
use App\Models\Category;
use App\Models\Bundle;
use Sofa\Eloquence\Eloquence;

class Media extends Model
{
    use HistoryTrait;
    use Eloquence;

    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'media';

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
        'type',
        'url',
        'url_xxs',
        'url_xs',
        'url_sm',
        'url_md',
        'url_lg',
        'url_xl',
        'user_id',
        'title',
        'description',
        'reps',
        'disabled_at',
        'filename',
        'height',
        'width',
        'size',
        'extension',
        'expires_at',
        'uploaded_as_attachment',
        'mediable_id',
        'mediable_type',
        'is_public'
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
     * The attributes that should be mutated to dates
     * E.g. deleted_at, published_at, etc
     *
     * @var array
     */
    protected $dates = [];

    /**
     * The attributes that should be hidden for arrays
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
     * The accessors to append to the model's array form
     *
     * @var array
     */
    protected $appends = [];

    /**
     * The relations to eager load on every query.
     * Use conservatively.
     *
     * @var array
     */
    protected $with = [];

    /**
     * The rules to apply for validation.
     *
     * @var array
     */
    public static $rules = [];


    protected $searchableColumns = [
        'title',
        'description',
        'filename'
    ];

    /*
    |--------------------------------------------------------------------------
    | Accessors and Mutators
    |--------------------------------------------------------------------------
    |
    | These are methods used to alter existing properties before returning
    | them or to create pseudo-properties for the model.
    |
    */

    /*
    TODO: This needs to be refactored to associate a dimension in pixels
          with the string that will be appended to the file name.
    */
    /**
     * An array of 'tags' for each of the various image sizes we want.
     *
     * @return array
     */
    protected static function getImageSizes()
    {
        return [
            '', // original
            '_xxs',
            '_sm',
            '_xs',
            '_md',
            '_lg',
            '_xl',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    | These are methods that are an alias to more complicated operations
    | to a simple Eloquent method for the model.
    |
    */

    //

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */

    public function owner()
    {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->morphMany(Tag::class, 'taggable');
    }

    public function products()
    {
        return $this->morphedByMany(Product::class, 'mediable');
    }

    public function bundles()
    {
        return $this->morphedByMany(Bundle::class, 'mediable');
    }

    public function categories()
    {
        return $this->morphedByMany(Category::class, 'mediable');
    }
}
