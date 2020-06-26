<?php

namespace App\Models;

class SubscriptionLine extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'autoship_subscription_lines';

    /**
     * The attributes that are mass assignable
     *
     * @var array
     */
    protected $fillable = [
        'pid',
        'autoship_subscription_id',
        'disabled_at',
        'inventory_owner_pid',
        'items',
        'item_id',
        'price',
        'quantity',
        'tax_class',
        'bundle_id',
        'bundle_name',
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
    public static $rules = [
        'item_id'           => 'required|integer',
        'price'             => 'required|numeric',
        'quantity'          => 'required|integer|min:1',
        'subscription_pid'  => 'required|integer|exists:autoship_subscriptions,pid',
        'disable'           => 'sometimes|required|in:true,false,1,0,'.true.','.false,
        'items'             => 'required|array',
        'items.*.inventory_id' => 'required|integer',
        'items.*.product_name' => 'required|string',
        'items.*.variant_name' => 'required|string',
        'items.*.option_label' => 'required|string',
        'items.*.option' => 'required|string',
        'items.*.sku' => 'required|string',
    ];

    /**
     * The columns to return from the database.
     *
     * @var array
     */
    public static $selects = [
        'autoship_subscription_lines.item_id',
        'autoship_subscription_lines.pid',
        'autoship_subscription_lines.price',
        'autoship_subscription_lines.quantity',
        'autoship_subscription_lines.autoship_subscription_id',
        'autoship_subscription_lines.created_at',
        'autoship_subscription_lines.updated_at',
        'autoship_subscription_lines.disabled_at',
        'autoship_subscription_lines.inventory_owner_pid',
        'autoship_subscription_lines.items',
        'autoship_subscription_lines.tax_class',
        'autoship_subscription_lines.bundle_id',
        'autoship_subscription_lines.bundle_name',
    ];

    protected $casts = [
        'items' => 'object',
        'price' => 'double'
    ];

    public function getPriceAttribute()
    {
        return round($this->attributes['price'], 2);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */

    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'autoship_subscription_id', 'id');
    }
}
