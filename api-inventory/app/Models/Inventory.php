<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use SoftDeletes;

    protected static function boot()
    {
        parent::boot();
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
                    'auth_user_id'                  => isset(app('request')->user->id) ? app('request')->user->id : null,
                    'request_id'                    => app('request')->headers->get('X-Cp-Request-Id'),
                    'request_path'                  => app('request')->path(),
                    'application'                   => 'Inventory API'
                ]);
            } catch (\Exception $e) {
                app('log')->error('inventory history create error', [
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
     * The attributes that are mass assignable
     *
     * @var array
     */
    protected $fillable = [
        'quantity_available',
        'user_id',
        'owner_id',
        'user_pid',
        'owner_pid',
        'item_id',
        'disabled_at',
        'inventory_price',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'owner_id',
        'quantity_staged',
        'deleted_at',
        'disabled_at',
        'created_at',
        'updated_at',
        'purchased_at',
        'expires_at',
        'locked_for_processing',
        'quantity_imported',
    ];

    protected $casts = [
        'quantity_avialable' => 'integer',
        'item_id' => 'integer',
    ];

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

    public function increment($column, $amount = 1, array $extra = [])
    {
        $before = $this->original;
        $change = parent::increment($column, $amount, $extra);
        $after = $this->original;
        try {
            InventoryHistory::create([
                'inventory_id'                  => $before['id'],
                'inventory_user_id'             => $before['user_id'],
                'item_id'                       => $before['item_id'],
                'before_quantity_available'     => $before['quantity_available'],
                'after_quantity_available'      => $after['quantity_available'],
                'before_quantity_staged'        => $before['quantity_staged'],
                'after_quantity_staged'         => $after['quantity_staged'],
                'auth_user_id'                  => isset(app('request')->user->id) ? app('request')->user->id : null,
                'request_id'                    => app('request')->headers->get('X-Cp-Request-Id'),
                'request_path'                  => app('request')->path(),
                'application'                   => 'Inventory API'
            ]);
        } catch (\Exception $e) {
            app('log')->error('inventory history increment error', [
                'message' => $e->getMessage(),
                'fingerprint' => 'inventory history increment error'
            ]);
        }
        return $change;
    }
}
