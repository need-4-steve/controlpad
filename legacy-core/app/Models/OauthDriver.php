<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OauthDriver extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'oauth_drivers';

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
        'name',
        'keyname',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */

    public function tokens()
    {
        return $this->hasMany(OauthToken::class, 'driver_id', 'id');
    }
}
