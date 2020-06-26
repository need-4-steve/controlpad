<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatchLabel extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'batch_labels';


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
        'shippo_id',
        'status',
        'label_url',
        'amount',
        'markup',
        'total_price',
        'parcel_template_id',
        'weight',
        'mass_unit'
    ];

    public static $rules = [
        'id' => 'required|integer',
        'carrier_id' => 'required|integer',
        'service_level_id' => 'required|integer',
        'parcel_template_id' => 'required|integer',
        'shipments' => 'required',
        'shipments.*.id' => 'required|integer',
        'shipments.*.weight' => 'numeric|nullable',
        'shipments.*.mass_unit' => 'in:g,oz,lb,kg|nullable',
        'shipments.*.carrier_id' => 'integer|nullable',
        'shipments.*.service_level_id' => 'integer|nullable',
        'shipments.*.parcel_template_id' => 'integer|nullable'
    ];

    /**
     * The attributes that should be hidden for arrays
     *
     * @var array
     */
    protected $hidden = [
        'amount',
        'markup',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */
    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'batch_shipments', 'batch_label_id', 'order_id');
    }

    public function parcel()
    {
        return $this->belongsTo(ParcelTemplate::class, 'parcel_template_id');
    }

    public function service()
    {
        return $this->belongsTo(ServiceLevel::class, 'service_level_id');
    }

    public function shipments()
    {
        return $this->hasMany(BatchLabelShipment::class);
    }
}
