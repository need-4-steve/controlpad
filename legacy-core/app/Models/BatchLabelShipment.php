<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatchLabelShipment extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'batch_shipments';


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
        'batch_label_id',
        'order_id',
        'parcel_template_id',
        'weight',
        'mass_unit',
        'carrier_id',
        'service_level_id'
    ];

    public static $rules = [];

    /**
     * The attributes that should be hidden for arrays
     *
     * @var array
     */
    protected $hidden = [];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */

    public function batchLabel()
    {
        return $this->belongsTo(BatchLabel::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
