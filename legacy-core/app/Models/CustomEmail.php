<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HistoryTrait;

class CustomEmail extends Model
{
    use HistoryTrait;

    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'custom_emails';

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
        'title',
        'greeting',
        'content_1',
        'content_2',
        'signature',
        'subject',
        'send_email',
        'display_name',
        'body',
        'revised_at'
    ];
}
