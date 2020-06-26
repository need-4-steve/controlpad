<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HistoryTrait;
use App\Models\Setting;

class Visibility extends Model
{

    const MAP = [
        'Customer' => 3,
        'Reseller Retail' => 3,
        'Rep' => 5,
        'Wholesale' => 5,
        'Admin' => 7,
        'Superadmin' => 8,
        'Corp Retail' => 1,
        'Affiliate' => 2,
        'Preferred Retail' => 6
    ];
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'visibilities';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];


    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'pivot',
        'description',
        'created_at',
        'updated_at'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */
}
