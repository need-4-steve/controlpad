<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLogs extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'email_log';

    /**
     * The primary key for the model
     *
     * @var string
     */
    protected $primaryKey = 'id';
}
