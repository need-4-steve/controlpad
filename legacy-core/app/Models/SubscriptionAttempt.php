<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionAttempt extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'subscription_attempts';

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
        'subscription_user_id',
        'description',
        'subscription_receipts_id'
    ];
}
