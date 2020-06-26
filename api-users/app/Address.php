<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
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
     * Compares two addresses. Returns false if they are different.
     */
    public static function compare($address_1, $address_2)
    {
        // convert both addresses into a new objects
        $address_1 = json_decode(json_encode($address_1));
        $address_2 = json_decode(json_encode($address_2));
        // define keys to compare
        $keys = ['line_1', 'line_2', 'city', 'state', 'zip'];
        foreach ($keys as $key) {
            if (isset($address_1->$key) && isset($address_2->$key) && $address_1->$key !== $address_2->$key) {
                return false;
            }
        }
        return true;
    }
}
