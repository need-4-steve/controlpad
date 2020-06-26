<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrier extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'carriers';


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
        'account_id',
        'token',
        'name',
    ];

    public static $rules = [];

    protected $hidden = [
        'account_id'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */
    public function parcelTemplate()
    {
        return $this->hasMany(ParcelTemplate::class);
    }

    public function serviceLevel()
    {
        return $this->hasMany(ServiceLevel::class);
    }
}
