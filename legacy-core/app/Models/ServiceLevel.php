<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceLevel extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'service_levels';


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
        'carrier_id',
        'token',
        'name',
    ];

    public static $rules = [];

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
        return $this->hasOne(Carrier::class);
    }
}
