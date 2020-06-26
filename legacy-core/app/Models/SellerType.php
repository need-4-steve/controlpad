<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HistoryTrait;

class SellerType extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'seller_types';

    /**
     * The primary key for the model
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description'
    ];
}
