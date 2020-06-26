<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cartline extends Model
{
    public static $rules = [];

    protected $fillable = [
        'cart_id',
        'bundle_name',
        'tax_class',
        'pid',
        'inventory_owner_pid',
        'item_id',
        'bundle_id',
        'quantity',
        'price',
        'event_id',
        'discount',
        'discount_type_id',
    ];

    protected $hidden = [
        'id',
        'cart_id'
    ];

    protected $casts = [
        'price' => 'double'
    ];

    public static $createRules = [
        '*.item_id' => 'required_without:*.bundle_id|integer',
        '*.bundle_id' => 'required_without:*.item_id|integer',
        '*.price' => 'sometimes|numeric|min:0',
        '*.quantity' => 'required|integer|min:1',
        '*.discount' => 'sometimes|numeric|min:0',
        '*.discount_type_id' => 'sometimes|integer|in:1,2',
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
