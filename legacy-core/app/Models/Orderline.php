<?php namespace App\Models;

use Cache;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\EnabledTrait;
use App\Models\Traits\HistoryTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sofa\Eloquence\Eloquence;

class Orderline extends Model
{
    use SoftDeletes;
    use EnabledTrait;
    use HistoryTrait;
    use Eloquence;

    /**
     * The database table used by the model.
     *
     * @var string
     */

    protected $table = 'orderlines';

    /**
     * The primary key for the model
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */ 
    protected $fillable = [
        'pid',
        'order_id',
        'item_id',
        'type',
        'name',
        'price',
        'quantity',
        'custom_sku',
        'manufacturer_sku',
        'bundle_name',
        'bundle_id',
        'inventory_owner_id',
        'inventory_owner_pid',
        'discount_amount',
        'discount_type_id',
        'in_comm_engine',
        'variant',
        'option',
        'event_id',
        'items'
    ];

    /**
     * Additional attributes
     *
     * @var array
     */
    protected $searchableColumns = [];
    /*
     |--------------------------------------------------------------------------
     | Accessors and Mutators
     |--------------------------------------------------------------------------
     |
     | These are methods used to alter existing properties before returning
     | them or to create pseudo-properties for the model.
     |
     */
    protected $attributes = [];

    /**
     * The accessors to append to the model's array form
     *
     * @var array
     */
    protected $appends = [
        'quantity_remaining'
     ];

    /*
    |--------------------------------------------------------------------------
    | Accessors and Mutators
    |--------------------------------------------------------------------------
    |
    | These are methods used to alter existing properties before returning
    | them or to create pseudo-properties for the model.
    |
    */

    public function getQuantityRemainingAttribute()
    {
        return $this->attributes['quantity'] - $this->returnlines()->sum('quantity');
    }

    public function getItemsAttribute()
    {
        return json_decode($this->attributes['items']);
    }

    public function setItemsAttribute($value)
    {
        $this->attributes['items'] = json_encode($value);
    }

    /*
    |------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    | These are methods that are an alias to more complicated operations
    | to a simple Eloquent method for the model.
    |
    */

    //

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function bundle()
    {
        return $this->belongsTo(Bundle::class);
    }

    public function returns()
    {
        return $this->hasMany(ReturnModel::class, 'orderline_id');
    }

    public function returnlines()
    {
        return $this->hasMany(Returnline::class);
    }
    public function owner()
    {
        return $this->hasOne(User::class, 'id', 'inventory_owner_id');
    }
}
