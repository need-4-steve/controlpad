<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "name",
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];

    public static $updateRules = [
        'name' => 'sometimes|string'
    ];

    public static $updateFields = [
        'name'
    ];

}
