<?php

namespace App\Models;

use Sofa\Eloquence\Eloquence;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use Eloquence;

    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'media';

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
        'type',
        'url',
        'url_xxs',
        'url_xs',
        'url_sm',
        'url_md',
        'url_lg',
        'url_xl',
        'user_id',
        'title',
        'description',
        'reps',
        'disabled_at',
        'filename',
        'height',
        'width',
        'size',
        'extension',
        'expires_at',
        'uploaded_as_attachment',
        'mediable_id',
        'mediable_type',
        'is_public'
    ];

    /**
     * The attributes that should be hidden for arrays
     *
     * @var array
     */
    protected $hidden = [
        'pivot',
        'type',
        'url_xxs',
        'url_xs',
        'url_sm',
        'url_md',
        'url_lg',
        'url_xl',
        'user_id',
        'title',
        'description',
        'reps',
        'disabled_at',
        'filename',
        'height',
        'width',
        'size',
        'extension',
        'expires_at',
        'uploaded_as_attachment',
        'mediable_id',
        'mediable_type',
        'is_public',
        'created_at',
        'updated_at',
    ];

    /**
     * The rules to apply for validation.
     *
     * @var array
     */
    public static $rules = [];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */
}
