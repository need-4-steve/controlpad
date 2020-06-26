<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'order_status';
    
    /**
     * The primary key for the model
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'position',
        'visible'
    ];

    public static $rules = [
        'name'        => 'required|string|unique:order_status,name',
        'position' => 'required|integer',
        'visible'     => 'required|boolean',
    ];
}
