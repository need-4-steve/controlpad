<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserStatus extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'user_status';
    
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
        'position',
        'visible',
        'login',
        'buy',
        'sell',
        'renew_subscription',
        'rep_locator'
    ];

    public static $rules = [
        'name'                  => 'required|string|unique:user_status,name',
        'position'              => 'required|integer',
        'visible'               => 'required|boolean',
        'login'                 => 'required|boolean',
        'buy'                   => 'required|boolean',
        'sell'                  => 'required|boolean',
        'renew_subscription'    => 'required|boolean',
        'rep_locator'           => 'required|boolean',
    ];
}
