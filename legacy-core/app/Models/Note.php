<?php namespace App\Models;

use Auth;
use Eloquent;
use App\Models\Traits\HistoryTrait;

class Note extends Eloquent
{
    use HistoryTrait;

    protected $table = 'notes';

    protected $fillable = [
        'body',
        'noteable_id',
        'noteable_type',
        'user_id'
    ];

    public static $rules = [
       'body' => 'required',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */

    public function notes()
    {
        return $this->morphTo();
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
