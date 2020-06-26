<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Orderline extends Model
{
    use SoftDeletes;

    protected $fillable = [

    ];

    protected $hidden = [
        'order_id'
    ];

    protected $casts = [
        'price' => 'double',
        'discount' => 'double',
        'premium_shipping_amount' => 'double',
    ];

    public function getItemsAttribute()
    {
        return json_decode($this->attributes['items']);
    }

    public function setItemsAttribute($value)
    {
        $this->attributes['items'] = json_encode($value);
    }
}
