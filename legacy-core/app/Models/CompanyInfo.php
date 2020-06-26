<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyInfo extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'company_info';

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
        'ein',
        'user_id'
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
        return $this->belongsTo(User::class, 'id', 'user_id');
    }
}
