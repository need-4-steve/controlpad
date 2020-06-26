<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LiveVideo extends Model
{
    use SoftDeletes;
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'live_videos';

    /**
     * The primary key for the model
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'oauth_driver_id',
        'video_id',
        'thumbnail',
        'description',
        'service_save_later',
        'deleted_at',
    ];

    /**
     * The attributes that should be cast to native types
     * E.g. always cast a number as integer instead
     * of a string
     *
     * Your options are:
     * integer, real, float, double, string,
     * boolean, object, array, collection,
     * date, datetime, timestamp
     *
     * @var array
     */
    protected $casts = [
        'user_id'            => 'integer',
        'oauth_driver_id'    => 'integer',
        'service_save_later' => 'boolean',
    ];

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
    protected $appends = [
        'formatted_created_at'
    ];

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

    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->diffForHumans();
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

    /**
     * Search for a token based on the driver's key name.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|int                            $driver
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereDriver($query, $driver)
    {
        $column = 'keyname';
        if (is_integer($driver)) {
            $column = 'id';
        }

        return $query->whereHas('driver', function ($query) use ($column, $driver) {
            $query->where($column, $driver);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */

    public function driver()
    {
        return $this->belongsTo(OauthDriver::class, 'oauth_driver_id', 'id');
    }

    public function inventory()
    {
        return $this->belongsToMany(Inventory::class);
    }

    public function liveVideoInventory()
    {
        return $this->hasMany(LiveVideoInventory::class);
    }
    public function liveVideoProduct()
    {
        return $this->belongsToMany(LiveVideoProduct::class, 'product_video');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
