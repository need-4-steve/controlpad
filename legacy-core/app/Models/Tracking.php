<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\EnabledTrait;
use App\Models\Traits\HistoryTrait;

class Tracking extends Model
{
    public static $rules = [
        'order_id' => 'required|int',
        'number' => 'required|string',
        'url' => 'sometimes|url',
    ];

    protected $table = 'tracking';

    protected $fillable = [
        'order_id',
        'number',
        'url'
    ];
}
