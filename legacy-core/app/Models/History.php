<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class History extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'history';

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
        'action',
        'historable_type',
        'historable_id',
        'user_id',
        'before',
        'after'
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
    protected $dates = [
        'deleted_at',
        'created_at',
        'updated_at'
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

    /**
     * The rules to apply for validation.
     *
     * @var array
     */
    public static $rules = [];

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

    public function historable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // less intensive relationship for getting just the user's name
    public function username()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')
            ->select([
                'id',
                'first_name',
                'last_name'
            ]);
    }
}
