<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KeyService extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "api_key",
        "service_id"
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];

}
