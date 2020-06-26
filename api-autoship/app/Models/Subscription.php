<?php

namespace App\Models;

use DB;

class Subscription extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'autoship_subscriptions';

    /**
     * The attributes that are mass assignable
     *
     * @var array
     */
    protected $fillable = [
        'pid',
        'buyer_first_name',
        'buyer_last_name',
        'buyer_pid',
        'cycle',
        'disabled_at',
        'discounts',
        'duration',
        'free_shipping',
        'frequency',
        'inventory_user_pid',
        'next_billing_at',
        'percent_discount',
        'autoship_plan_id',
        'seller_pid',
        'type',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'autoship_plan_id',
    ];

    /**
     * The rules to apply for validation.
     *
     * @var array
     */
    public static $rules = [
        'buyer_first_name'      => 'required|string',
        'buyer_last_name'      => 'required|string',
        'disable'               => 'sometimes|required|in:true,false,1,0,'.true.','.false,
        'cart_pid'              => 'required|string',
        'next_billing_at'       => 'required|after:yesterday',
        'plan_pid'              => 'nullable|exists:autoship_plans,pid',
        // required with no plan
        'discounts'             => 'required_without:plan_pid|array',
        'duration'              => 'required_without:plan_pid|in:Days,Weeks,Months,Quarters,Years',
        'free_shipping'         => 'required_without:plan_pid||in:true,false,1,0,'.true.','.false,
        'frequency'             => 'required_without:plan_pid|integer',
    ];

    /**
     * The columns to return from the database.
     *
     * @var array
     */
    public static $selects = [
        'autoship_subscriptions.id',
        'autoship_subscriptions.autoship_plan_id',
        'autoship_subscriptions.pid',
        'autoship_subscriptions.buyer_first_name',
        'autoship_subscriptions.buyer_last_name',
        'autoship_subscriptions.buyer_pid',
        'autoship_subscriptions.created_at',
        'autoship_subscriptions.cycle',
        'autoship_subscriptions.disabled_at',
        'autoship_subscriptions.discounts',
        'autoship_subscriptions.duration',
        'autoship_subscriptions.free_shipping',
        'autoship_subscriptions.frequency',
        'autoship_subscriptions.inventory_user_pid',
        'autoship_subscriptions.next_billing_at',
        'autoship_subscriptions.percent_discount',
        'autoship_subscriptions.seller_pid',
        'autoship_subscriptions.type',
        'autoship_subscriptions.updated_at',
    ];

    protected $searchableColumns = [
        'pid' => 5,
        'buyer_first_name' => 5,
        'buyer_last_name' => 5,
        'buyer_pid' => 4,
        'seller_pid' => 4,
        'type' => 1,
    ];

    protected $casts = [
        'discounts' => 'object',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */

    public function attempts()
    {
        return $this->hasMany(Attempt::class, 'autoship_subscription_id');
    }

    public function lines()
    {
        return $this->hasMany(SubscriptionLine::class, 'autoship_subscription_id');
    }
}
