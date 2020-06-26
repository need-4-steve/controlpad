<?php

namespace App\Models;

use App\Models\Traits\EnabledTrait;
use App\Models\Traits\HistoryTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sofa\Eloquence\Eloquence;

class Inventory extends Model
{
    use EnabledTrait;
    use HistoryTrait;
    use SoftDeletes;
    use Eloquence;

    protected static function boot()
    {
        parent::boot();
        static::updated(function ($model) {
            try {
                $before = $model->getOriginal();
                $after = $model->getDirty();
                // Check if inventory has changed before writing to the History.
                if (isset($after['quantity_available']) &&
                    $after['quantity_available'] !== $before['quantity_available'] ||
                    isset($after['quantity_staged']) &&
                    $after['quantity_staged'] !== $before['quantity_staged']
                ) {
                    InventoryHistory::create([
                        'inventory_id'                  => $before['id'],
                        'inventory_user_id'             => $before['user_id'],
                        'item_id'                       => $before['item_id'],
                        'before_quantity_available'     => $before['quantity_available'],
                        'after_quantity_available'      => isset($after['quantity_available']) ? $after['quantity_available'] : $before['quantity_available'],
                        'before_quantity_staged'        => $before['quantity_staged'],
                        'after_quantity_staged'         => isset($after['quantity_staged']) ? $after['quantity_staged'] : $before['quantity_staged'],
                        'auth_user_id'                  => auth()->check() ? auth()->user()->id : null,
                        'request_email'                 => request()->input('user.email'),
                        'request_id'                    => request()->headers->get('X-Cp-Request-Id'),
                        'request_path'                  => request()->path(),
                        'application'                   => 'Core'
                    ]);
                }
            } catch (\Exception $e) {
                logger()->error('inventory history update error', [
                    'message' => $e->getMessage(),
                    'fingerprint' => 'inventory history update error'
                ]);
            }
        });
        static::created(function ($model) {
            try {
                $after = $model->getDirty();
                InventoryHistory::create([
                    'inventory_id'                  => $after['id'],
                    'item_id'                       => $after['item_id'],
                    'inventory_user_id'             => $after['user_id'],
                    'before_quantity_available'     => 0,
                    'after_quantity_available'      => isset($after['quantity_available']) ? $after['quantity_available'] : 0,
                    'before_quantity_staged'        => 0,
                    'after_quantity_staged'         => isset($after['quantity_staged']) ? $after['quantity_staged'] : 0,
                    'auth_user_id'                  => auth()->check() ? auth()->user()->id : null,
                    'request_email'                 => request()->input('user.email'),
                    'request_id'                    => request()->headers->get('X-Cp-Request-Id'),
                    'request_path'                  => request()->path(),
                    'application'                   => 'Core'
                ]);
            } catch (\Exception $e) {
                logger()->error('inventory history create error', [
                    'message' => $e->getMessage(),
                    'fingerprint' => 'inventory history create error'
                ]);
            }
        });
    }

    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'inventories';

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
        'user_id',
        'user_pid',
        'owner_id',
        'owner_pid',
        'item_id',
        'quantity_available',
        'quantity_staged',
        'disabled_at',
        'owner_id',
        'expires_at',
        'purchased_at',
        'locked_for_processing',
        'quantity_imported',
        'inventory_price',
    ];

    protected $searchableColumns = [
        'item.product.name',
        'owner.first_name',
        'owner.last_name'
    ];
    /**
     * The rules to apply for validation.
     *
     * @var array
     */
    public static $rules = [
        'user_id' => 'required|numeric|min:1|exists:user,id',
        'item_id' => 'required|numeric|min:1|exists:item,id'
    ];


    public function scopeInStock($query, $userId)
    {
        return $query
                ->where('inventories.user_id', '=', $userId)
                ->where('inventories.expires_at', '>', date("Y-m-d H:i:s"))
                ->whereNotNull('inventories.expires_at')
                ->whereNull('inventories.disabled_at')
                ->orWhere('inventories.user_id', '=', $userId)
                ->whereNull('inventories.expires_at')
                ->whereNull('inventories.disabled_at');
    }
    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function price()
    {
        return $this->hasOne(Price::class, 'priceable_id', 'id')
            ->where('priceable_type', Inventory::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id')->withTrashed();
    }
}
