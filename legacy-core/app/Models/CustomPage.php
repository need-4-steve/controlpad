<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HistoryTrait;

class CustomPage extends Model
{
    use HistoryTrait;

    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'custom_pages';

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
        'content',
        'slug',
        'revised_at'
    ];
}
