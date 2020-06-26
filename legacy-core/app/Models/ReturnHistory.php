<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnHistory extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'return_history';

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
        'user_id',
        'new_status_id',
        'old_status_id',
        'comments',
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
        'return_id'     => 'integer',
        'user_id'       => 'integer',
        'new_status_id' => 'integer',
        'old_status_id' => 'integer',
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
     * The User that initiated this return request.
     *
     * @method initiatorUser
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function initiatorUser()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The current (new) ReturnStatus of this return request.
     *
     * @method newStatus
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function newStatus()
    {
        return $this->belongsTo(ReturnStatus::class, 'new_status_id', 'id');
    }

    /**
     * The previous (old) ReturnStatus of this return reqeust.
     * This can be null but only on the initial creation
     * of the record.
     *
     * @method oldStatus
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function oldStatus()
    {
        return $this->belongsTo(ReturnStatus::class, 'old_status_id', 'id');
    }
}
