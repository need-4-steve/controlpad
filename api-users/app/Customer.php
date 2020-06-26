<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{

    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'customer_id',
    ];

    protected $hidden = [
        'id'
    ];

    public static $createRules = [
        'user_id' => 'required',
        'customer' => 'filled|required',
        'customer.email' => 'required|email',
        'customer.first_name' => 'required',
        'customer.last_name' => 'required',
        'phone.number' => 'sometimes|min:10|max:11',
        'customer.shipping_address' => 'sometimes|filled',
        'customer.shipping_address.line_1' => 'required_with:shipping_address',
        'customer.shipping_address.city' => 'required_with:shipping_address',
        'customer.shipping_address.state' => 'required_with:shipping_address',
        'customer.shipping_address.zip' => 'required_with:shipping_address',
        'customer.billing_address' => 'sometimes|filled',
        'customer.billing_address.line_1' => 'required_with:billing_address',
        'customer.billing_address.city' => 'required_with:billing_address',
        'customer.billing_address.state' => 'required_with:billing_address',
        'customer.billing_address.zip' => 'required_with:billing_address',
    ];
}
