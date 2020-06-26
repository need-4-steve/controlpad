<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZoomUser extends Model
{
    protected $table = 'zoom_user';

    protected $fillable = [
        'user_id',
        'email',
        'zoom_user_id',
        'woocom_customer_id'
    ];
}
