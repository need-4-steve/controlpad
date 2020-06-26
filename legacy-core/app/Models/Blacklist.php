<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blacklist extends Model
{
    protected $table = 'blacklisted';

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
        'created_at',
        'updated_at',
        'created_at',
        'category'
    ];

    /**
     * The rules to apply for validation.
     *
     * @var array
     */
}
