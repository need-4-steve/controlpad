<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visibility extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'autoship_visibilities';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'autoship_plan_id',
        'visibility_id'
    ];


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
