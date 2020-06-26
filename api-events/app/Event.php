<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sponsor_id',
        'host_id',
        'name',
        'description',
        'img',
        'location',
        'host_name',
        'sale_start',
        'sale_end',
        'date',
        'items_limit',
        'items_purchased',
        'status',
        'product_ids'
    ];

    public static $createFields = [
        'host_id',
        'name',
        'description',
        'img',
        'location',
        'host_name',
        'sale_start',
        'sale_end',
        'items_limit',
        'date',
        'product_ids',
    ];

    // fields acceptable for the client to update.
    public static $updateFields = [
        'host_id',
        'name',
        'description',
        'img',
        'location',
        'host_name',
        'sale_start',
        'sale_end',
        'date',
        'items_limit',
        'status',
        'product_ids',
    ];

    public static $rules = [
        'host_id' => 'nullable',
        'name' => 'required|string',
        'description' => 'string|nullable',
        'img' => 'sometimes|string|nullable',
        'location' => 'string',
        'host_name' => 'sometimes|string|nullable',
        'sale_start' => 'required|date',
        'sale_end' => 'required|date',
        'date' => 'sometimes|date',
        'product_ids' => 'sometimes|nullable'
    ];

    public static $updateRules = [
        'host_id' => 'nullable',
        'name' => 'required|string',
        'description' => 'string|nullable',
        'img' => 'sometimes|string|nullable',
        'location' => 'string',
        'host_name' => 'sometimes|string|nullable',
        'sale_start' => 'required|date',
        'sale_end' => 'required|date',
        'date' => 'sometimes|date',
        'status' => 'sometimes|in:closed',
        'product_ids' => 'sometimes|nullable'
    ];

    protected $hidden = [];

    public function getProductIdsAttribute()
    {
        return json_decode($this->attributes['product_ids']);
    }

    public function setProductIdsAttribute($value)
    {
        $this->attributes['product_ids'] = json_encode($value);
    }
}
