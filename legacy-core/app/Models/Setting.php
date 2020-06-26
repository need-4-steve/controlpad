<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HistoryTrait;

class Setting extends Model
{
    use HistoryTrait;

    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'settings';

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
        'user_id',
        'key',
        'value',
        'category',
    ];

    /**
     * The rules to apply for validation.
     *
     * @var array
     */
    public static $rules = [
        'user_id'   => 'required',
        'key'       => 'required',
    ];
}
