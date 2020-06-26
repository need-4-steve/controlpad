<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $table = 'invoices';
    protected $fillable = [
    ];

    protected $hidden = [
    ];

    public static $indexParams = [
        'per_page',
        'customer_id',
        'store_owner_user_id',
        'type',
        'inventory_user_pid',
        'start_date',
        'end_date'
    ];

    protected $casts = [
        'subtotal_price' => 'double',
        'total_discount' => 'double',
        'total_shipping' => 'double',
    ];

    public function isExpired()
    {
        return $this->expires_at != null && \Carbon\Carbon::now()->setTimezone('UTC')->gt($this->expires_at);
    }
}
