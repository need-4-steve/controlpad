<?php

namespace App\Models;

class Example extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'examples';

    /**
     * The attributes that are mass assignable
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The rules to apply for validation.
     *
     * @var array
     */
    public static $rules = [];

    /**
     * The columns to return from the database.
     *
     * @var array
     */
    public static $selects = [];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */
}
