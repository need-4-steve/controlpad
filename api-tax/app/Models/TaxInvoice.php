<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxInvoice extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'tax_connection_id'
    ];

    protected $casts = [
        'subtotal' => 'double',
        'tax' => 'double'
    ];

    public static $updateRules = [

    ];

    public static $updateFields = [

    ];

    public static $createRules = [
        'to_address' => 'filled|required_without:single_location',
        'to_address.city' => 'filled|nullable',
        'to_address.zip' => 'required_with:to_address',
        'to_address.state' => 'required_with:to_address',
        'from_address' => 'filled|nullable',
        'from_address.city' => 'filled|nullable',
        'from_address.zip' => 'required_with:from_address',
        'from_address.state' => 'required_with:from_address',
        'single_location' => 'filled|nullable',
        'single_location.city' => 'filled|nullable',
        'single_location.zip' => 'required_with:single_location',
        'single_location.state' => 'required_with:single_location',
        'type' => 'required|in:sale,use,transfer,refund,refund-full',
        'merchant_id' => 'required',
        'commit' => 'required_unless:estimate,true,1|boolean',
        'line_items' => 'required',
        'line_items.*.subtotal' => 'required',
        'line_items.*.quantity' => 'required',
        'line_items.*.type' => 'in:discount,shipping'
    ];

    public static $createFields = [
        'customer_id',
        'order_pid',
        'line_items', // not sure how to filter at the object level
        'to_address.line_1',
        'to_address.line_2',
        'to_address.city',
        'to_address.state',
        'to_address.zip',
        'to_address.country',
        'from_address.line_1',
        'from_address.line_2',
        'from_address.city',
        'from_address.state',
        'from_address.zip',
        'from_address.country',
        'billing_address',
        'single_location.line_1',
        'single_location.line_2',
        'single_location.city',
        'single_location.state',
        'single_location.zip',
        'single_location.country',
        'merchant_id',
        'type',
        'commit'
    ];

    public static $refundRules = [
        'origin_pid' => 'required',
        'type' => 'required|in:refund,refund-full'
    ];

    public static $refundFields = [
        'origin_pid',
        'type',
        'line_items'
    ];

    public static $indexParams = [
        'per_page',
        'merchant_id',
        'committed',
        'type',
        'reference_id',
        'order_pid',
        'tax_connection_id'
    ];

    public function setRequestAttribute($value)
    {
        $this->attributes['request'] = (isset($value) ? json_encode($value) : null);
    }

    public function getRequestAttribute()
    {
        if (isset($this->attributes['request'])) {
            return json_decode($this->attributes['request'], 1);
        } else {
            return null;
        }
    }
}
