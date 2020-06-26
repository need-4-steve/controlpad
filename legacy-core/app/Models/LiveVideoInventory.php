<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LiveVideoInventory extends Model
{
    use SoftDeletes;
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'inventory_live_video';

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
        'live_video_id',
        'inventory_id',
        'sale_quantity',
        'discount_amount',
        'discount_is_percent',
        'user_id',
        'item_id',
        'deleted_at'
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
        'live_video_id'       => 'integer',
        'inventory_id'        => 'integer',
        'sale_quantity'       => 'integer',
        'discount_amount'     => 'double',
        'discount_is_percent' => 'boolean',
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
    protected $appends = [];

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

    //

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

    public function liveVideo()
    {
        return $this->belongsTo(LiveVideo::class);
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}
