<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnModel extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'returns';

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
        'order_id',
        'initiator_user_id',
        'return_status_id',
        'completed_at',
        'user_id'
    ];

    /**
     * The attributes that should be cast to native types
     * E.g. always cast a number as integer instead
     * of a string
     *
     * @var array
     */
    protected $casts = [
        'order_id'          => 'integer',
        'user_id'           => 'integer',
        'return_status_id'  => 'integer',
        'initiator_user_id' => 'interger',
    ];


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

    public function status()
    {
        return $this->belongsTo(ReturnStatus::class, 'return_status_id');
    }

    public function lines()
    {
        return $this->hasMany(Returnline::class, 'return_id', 'id');
    }

    public function history()
    {
        return $this->hasMany(ReturnHistory::class, 'return_id', 'id');
    }
}
