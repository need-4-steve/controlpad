<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HistoryTrait;

class UserSetting extends Model
{
    use HistoryTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_setting';

    /**
     * The primary key for the model
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'hide_products',
        'show_new_inventory',
        'show_address',
        'show_phone',
        'show_email',
        'show_location',
        'show_address_on_invoice',
        'will_deliver',
        'new_customer_message',
        'order_confirmation_message',
        'seller_type',
        'timezone',
        'payment_account',
        'user_pid',
    ];

    public function user()
    {
        return $this->belongsTo('user');
    }
}
