<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tracking extends Model
{
    public static $rules = [
        'order_id' => 'required|exists:orders,id',
        'number' => 'required|string',
        'shipped_at' => 'date|nullable',
        'url' => 'sometimes|url',
    ];

    protected $table = 'tracking';

    protected $fillable = [
        'order_id',
        'number',
        'shipped_at',
        'url'
    ];

    public static $updateFields = [
        'id',
        'order_id',
        'number',
        'shipped_at',
        'url'
    ];
}
