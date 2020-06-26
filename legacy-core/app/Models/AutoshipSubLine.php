<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutoshipSubLine extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'autoship_subscription_lines';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'id'
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
