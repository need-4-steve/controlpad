<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OauthToken extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'oauth_tokens';

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
        'user_id',
        'driver_id',
        'access_token',
        'refresh_token',
        'expires_at',
        'email',
        'service_email',
        'service_user_id',
        'issued_at',
    ];

    /**
     * The attributes that should be cast to native types
     * E.g. always cast a number as integer instead
     * of a string
     *
     * @var array
     */
    protected $casts = [
        'user_id'   => 'integer',
        'driver_id' => 'integer',
    ];

    /**
     * The attributes that should be mutated to dates
     * E.g. deleted_at, published_at, etc
     *
     * @var array
     */
    protected $dates = [
        'expires_at'
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
    |------------------------------------------------------------------------
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
     * @param string                                $driver
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereDriver($query, $driver)
    {
        return $query->whereHas('driver', function ($query) use ($driver) {
            $query->where('keyname', $driver);
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
        return $this->belongsTo(OauthDriver::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
