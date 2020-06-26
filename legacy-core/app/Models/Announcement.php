<?php namespace App\Models;

use Cache;
use Config;
use Eloquent;
use App\Models\Traits\HistoryTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Eloquent
{
    use HistoryTrait;
    use SoftDeletes;
    // Add your validation rules here.
    public static $rules = [
        'title' => 'required'
    ];

    // Don't forget to fill this array.
    protected $fillable = [
       'title',
       'url',
       'description',
       'body',
       'publish_date',
       'postCategory_id',
       'public',
       'customers',
       'hosts',
       'reps',
       'disabled',
       'deleted_at'
    ];
}
