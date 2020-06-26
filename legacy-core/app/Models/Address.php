<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HistoryTrait;
use App\Models\Traits\GeocodeTrait;

class Address extends Model
{
    use GeocodeTrait;

    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'addresses';

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
        'name',
        'address_1',
        'address_2',
        'city',
        'state',
        'addressable_id',
        'zip',
        'addressable_type',
        'label'
    ];

    /**
     * The rules to apply for validation.
     *
     * @var array
     */
    public static $rules = [
        'name'      => 'sometimes',
        'address_1' => 'required',
        'address_2' => 'sometimes',
        'city'      => 'required',
        'state'     => 'required',
        'zip'       => 'required'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */

    public function addressable()
    {
        return $this->morphTo();
    }
}
