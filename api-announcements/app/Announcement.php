<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'body',
        'description',
        'title',
    ];

    public static $createFields = [
        'body',
        'description',
        'title',
        'updated_at',
        'created_at',
        'deleted_at'
    ];

    // fields acceptable for the client to update.
    public static $updateFields = [
        'body',
        'description',
        'title',
        'updated_at',
        'deleted_at'
    ];

    public static $rules = [
        'title' => 'required|string',
        'body' => 'string',
        'description' => 'string',
    ];

    public static $updateRules = [
        'title' => 'sometimes|string',
        'body' => 'string',
        'description' => 'string',
    ];

    protected $hidden = [];
}
