<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// subscription token is actually saving a card token for a user to renew their subscription

class SubscriptionToken extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'subscription_token';
    /**
     * The attributes that are mass assignable
     *
     * @var array
     */
    protected $fillable = [
        'token',
        'user_id',
    ];
}
