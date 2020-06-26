<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservedItem extends Model
{
    /**
    * The table associated with the model
    *
    * @var string
    */
    protected $table = 'reserved_items';


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
        'cartline_id',
        'quantity'
    ];

    public static $rules = [];

    ################################################################################################
    # Relationships
    ################################################################################################

    public function cartline()
    {
        return $this->hasOne(Cartline::class, 'id', 'cartline_id');
    }
}
