<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HistoryTrait;
use App\Models\StoreSettingsKey;

class Setting extends Model
{

    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'user_setting';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'show_new_inventory',
        'show_address',
        'show_phone',
        'show_email',
        'show_location',
        'show_address_on_invoice',
        'will_deliver',
        'timezone',
        'payment_account',
        'user_pid',
    ];

    protected $hidden = [
        'id',
        'user_id',
        'new_customer_message',
        'order_confirmation_message',
        'payment_account',
    ];

    public static $updateRules = [
        'show_new_inventory' => 'boolean',
        'show_address' => 'boolean',
        'show_phone' => 'boolean',
        'show_email' => 'boolean',
        'show_location' => 'boolean',
        'show_address_on_invoice' => 'boolean',
        'will_deliver' => 'boolean',
        'timezone' => 'string',
        'payment_account' => 'boolean',
        'self_pickup' => 'boolean',
    ];
}
