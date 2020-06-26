<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HistoryTrait;
use App\Models\StoreSettingsKey;

class StoreSetting extends Model
{
    use HistoryTrait;

    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'store_settings';

    /**
    * The attributes that are mass assignable
    *
    * @var array
    */
    protected $fillable = ['value'];

    public function keys()
    {
        return $this->belongsTo(StoreSettingsKey::class, 'key_id');
    }
}
