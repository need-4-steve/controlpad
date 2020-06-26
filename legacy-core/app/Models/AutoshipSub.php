<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutoshipSub extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'autoship_subscriptions';

    protected $casts = [
        'discounts' => 'object',
    ];

    protected $appends = [
        'subtotal',
        'discount'
    ];

    public function getSubtotalAttribute()
    {
        $subtotal = 0;
        foreach ($this->lines as $line) {
            $subtotal += $line->quantity * $line->price;
        }
        return $subtotal;
    }

    public function getDiscountAttribute()
    {
        return round(($this->percent_discount / 100 * $this->subtotal), 2);
    }

    // public function attempts()
    // {
    //     return $this->hasMany(Attempt::class, 'autoship_subscription_id');
    // }

    public function lines()
    {
        return $this->hasMany(AutoshipSubLine::class, 'autoship_subscription_id');
    }
}
