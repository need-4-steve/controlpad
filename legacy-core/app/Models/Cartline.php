<?php namespace App\Models;

use Cache;
use Config;
use Eloquent;
use App\Models\Traits\EnabledTrait;
use App\Models\Traits\HistoryTrait;

class Cartline extends Eloquent
{
    use EnabledTrait;
    use HistoryTrait;

   // Add your validation rules here
    public static $rules = [
   // 'title' => 'required'
    ];

   // Don't forget to fill this array
    protected $table = 'cartlines';
    protected $fillable = [
       'pid',
       'cart_id',
       'item_id',
       'bundle_id',
       'bundle_name',
       'tax_class',
       'price',
       'quantity',
       'discount',
       'discout_type',
       'inventory_owner_id',
       'inventory_owner_pid',
       'event_id',
       'items'
    ];
    protected $appends = [
    ];

   /****************************
   * Relationships
   *****************************/

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function getItemsAttribute()
    {
        return json_decode($this->attributes['items']);
    }

    public function setItemsAttribute($value)
    {
        $this->attributes['items'] = json_encode($value);
    }
}
