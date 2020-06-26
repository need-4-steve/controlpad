<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use CPCommon\Pid\Pid;

class SubscriptionUser extends Model
{
    use SoftDeletes;

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->pid = Pid::create();
        });
    }

    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'subscription_user';

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
        'subscription_price',
        'auto_renew',
        'ends_at',
        'subscription_id',
        'user_id',
        'disabled_at',
        'last_fail_attempt',
        'pid',
        'user_pid',
    ];

    /**
     * The attributes that should be cast to native types
     * E.g. always cast a number as integer instead
     * of a string
     *
     * @var array
     */
    protected $casts = [
        'auto_renew'      => 'boolean',
        'subscription_id' => 'integer',
        'user_id'         => 'integer'
    ];

    /**
     * The attributes that should be mutated to dates
     * E.g. deleted_at, published_at, etc
     *
     * @var array
     */
    protected $dates = [
        'ends_at'
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

    public function scopeActive($query)
    {
        return $query->where('ends_at', '>', 'NOW()');
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class, 'id', 'subscription_id');
    }
    public function cardToken()
    {
        return $this->hasOne(CardToken::class, 'user_id', 'user_id');
    }
    public function attempts()
    {
        return $this->hasMany(SubscriptionAttempt::class, 'user_id', 'user_id');
    }
}
