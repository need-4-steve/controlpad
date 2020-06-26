<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomLinkType extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'custom_links_types';

    protected $fillable = [
        'name',
        'key',
    ];

    public $timestamps = false;
}
