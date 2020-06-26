<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\EnabledTrait;
use App\Models\Traits\HistoryTrait;

class Phone extends Model
{
    use EnabledTrait;
    use HistoryTrait;

    public static $rules = [
        'label' => 'required',
        'number' => 'required|numeric|digits:10',
        'type' => 'required',
    ];

    protected $table = 'phones';

    protected $fillable = [
        'label',
        'number',
        'type',
        'phonable_id',
        'phonable_type'
    ];

    public function getFormattedNumberAttribute($value)
    {
        return "(".substr($this->attributes['number'], 0, 3).") ".substr($this->attributes['number'], 3, 3)."-".substr($this->attributes['number'], 6, 4);
    }

    public function phonable()
    {
        return $this->morphTo();
    }

    protected $appends = [
        'formatted_number'
    ];
}
