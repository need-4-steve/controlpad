<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Returnline extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'return_lines';

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
        'return_id',
        'item_id',
        'orderline_id',
        'bundle_id',
        'return_reason_id',
        'type',
        'name',
        'price',
        'quantity',
        'custom_sku',
        'manufacturer_sku',
        'comments'
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
        'return_id' => 'integer',
        'item_id'   => 'integer',
        'bundle_id' => 'integer',
        'price'     => 'double',
        'quantity'  => 'integer',
    ];

    /**
     * The attributes that should be mutated to dates
     * E.g. deleted_at, published_at, etc
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

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

    /**
     * The return order request.
     *
     * @method return
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function return()
    {
        return $this->belongsTo(ReturnModel::class);
    }

    /**
     * The Item that is being returned.
     *
     * @method item
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * The Bundle that is being returned.
     *
     * @method bundle
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function bundle()
    {
        return $this->belongsTo(Bundle::class);
    }

    /**
     * The reason to be returned
     *
     * @method return_reason
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function reason()
    {
        return $this->hasOne(ReturnReason::class, 'id', 'return_reason_id');
    }
}
