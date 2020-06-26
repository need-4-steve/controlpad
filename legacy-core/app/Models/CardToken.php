<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CardToken extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'card_token';
    /**
     * The attributes that are mass assignable
     *
     * @var array
     */
    protected $fillable = [
        'token',
        'user_id',
        'type',
        'card_type',
        'card_digits',
        'expiration',
        'gateway_customer_id'
    ];

    /**
     * The rules to apply for validation.
     *
     * @var array
     */
    public static $rules = [
        'token'         => 'required',
        'user_id'       => 'required',
        'type'          => 'required',
        'card_type'     => 'sometimes',
        'card_digits'   => 'sometimes',
        'expiration'    => 'sometimes',
        'gateway_customer_id' => 'sometimes'
    ];
}
