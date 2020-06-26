<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LiveVideoProduct extends Model
{
    use SoftDeletes;
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'live_video_products';

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
        'price',
        'product_url',
        'user_id'
    ];

    public static $rules = [
        'name' => 'required',
        'price' => 'required',
        'product_url' => 'required',
        'user_id' => 'required'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */
    public function liveVideos()
    {
        return $this->belongsToMany(LiveVideo::class, 'product_video')->withPivot('live_video_id');
    }

    public function media()
    {
        return $this->morphToMany(Media::class, 'mediable');
    }
}
