<?php namespace App\Models;

use Cache;
use Config;
use App\Models\Traits\EnabledTrait;
use App\Models\Traits\HistoryTrait;
use App\Models\User;

class ShippingRate extends \Eloquent
{
    use EnabledTrait;
    use HistoryTrait;

    protected $table = 'shipping_rates';

    protected $fillable = [
            'id',
            'user_id',
            'user_pid',
            'amount',
            'min',
            'max',
            'type',
            'name'
    ];

     /**
     * The rules to apply for validation.
     *
     * @var array
     */
    public static $rules = [
        'amount' => 'required',
        'max' => 'required',
        'name' => 'required'
    ];

    /**
     * ***********************
     * Relationships
     **************************/
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
