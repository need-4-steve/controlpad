<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnStatus extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'return_statuses';

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
    protected $fillable = [];

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

    //
}
