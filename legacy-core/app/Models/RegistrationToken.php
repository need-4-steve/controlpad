<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationToken extends Model
{
    protected $table = 'registration_tokens';

    protected $fillable = [
        'token',
        'user_id',
        'source_id',
        'email',
        'first_name',
        'last_name',
        'address_1',
        'address_2',
        'city',
        'state',
        'zip',
        'phone',
        'public_id'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
