<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayQuickerToken extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'pay_quicker_tokens';
    /**
     * The attributes that are mass assignable
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'token'
    ];
}
