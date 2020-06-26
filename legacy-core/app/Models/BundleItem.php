<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BundleItem extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'bundle_item';

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
        'item_id',
        'bundle_id',
        'quantity'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
