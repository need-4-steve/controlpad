<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HistoryTrait;

class StoreSettingsKey extends Model
{
    use HistoryTrait;

    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'store_settings_keys';

    /**
     * The attributes that are mass assignable
     *
     * @var array
     */
    protected $fillable = ['key'];
}
