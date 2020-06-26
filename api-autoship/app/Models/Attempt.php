<?php

namespace App\Models;

class Attempt extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'autoship_attempts';

    /**
     * The attributes that are mass assignable
     *
     * @var array
     */
    protected $fillable = [
        'autoship_subscription_id',
        'description',
        'order_pid',
        'status',
        'subscription_cycle',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'autoship_subscription_id',
    ];

    /**
     * The rules to apply for validation.
     *
     * @var array
     */
    public static $rules = [];

    /**
     * The columns to return from the database.
     *
     * @var array
     */
    public static $selects = [];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */
}
