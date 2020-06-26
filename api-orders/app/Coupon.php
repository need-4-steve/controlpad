<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Coupon extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'owner_pid',
        'owner_id',
        'customer_id',
        'amount',
        'is_percent',
        'title',
        'description',
        'type',
        'uses',
        'max_uses',
        'expires_at'
    ];

    protected $hidden = [];

    protected $casts = [
        'amount' => 'double'
    ];

    public static $indexParams = [
        'per_page',
        'page',
        'owner_pid',
        'status',
        'search_term',
        'sort_by',
        'in_order'
    ];

    public static $indexRules = [
        'page' => 'required|integer',
        'per_page' => 'sometimes|integer|between:15,100',
        'owner_pid' => 'string|filled',
        'status' => 'sometimes|string|in:active,used,expired',
        'type' => 'sometimes|string|in:wholesale,retail',
        'search_term' => 'sometimes|string',
        'sort_by' => 'sometimes|string|in:created_at,-created_at,title,-title,code,-code,expires_at,-expires_at,type,-type'
    ];

    public static $createRules = [
        'code' => 'required|alpha_dash|max:255',
        'owner_pid' => 'string|filled',
        'customer_id' => 'nullable|integer|min:1',
        'amount' => 'required|numeric|min:0.01',
        'is_percent' => 'required|boolean',
        'title' => 'required|string',
        'description' => 'sometimes|string',
        'type' => 'required|string|in:wholesale,retail',
        'max_uses' => 'required|integer|min:1',
        'expires_at' => 'sometimes|nullable|date|after:now'
    ];

    public function isExpired()
    {
        return $this->expires_at != null && Carbon::now()->setTimezone('UTC')->gt($this->expires_at);
    }

    public function calculateDiscount($subtotal)
    {
        $discount = 0.00;
        // Calculate discount;
        if ($this->is_percent) {
            $discount += ($subtotal * ($this->amount / 100));
        } else {
            $discount += $this->amount;
        }
        $discount = round($discount, 2);
        if ($discount > $subtotal) {
            $discount = $subtotal;
        }
        return $discount;
    }
}
