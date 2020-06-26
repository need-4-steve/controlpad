<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomLink extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'custom_links';

    protected $fillable = [
        'name',
        'url',
        'type',
        'user_id',
        'open_in_new_tab'
    ];

    public static $rules = [
        'name' => 'required|string|max:255',
        'url' => 'required|url',
        'open_in_new_tab' => 'boolean'
    ];

    public static $updateFields = [
        'name',
        'url',
        'open_in_new_tab'
    ];

    public $timestamps = false;
}
