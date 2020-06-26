<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Visibility extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'visibilities';

    /**
     * The primary key for the model
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
    ];
}
