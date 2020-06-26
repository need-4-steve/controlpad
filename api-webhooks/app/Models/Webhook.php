<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{

    protected $fillable = [
        'name',
        'event',
        'url',
        'config',
        'active',
        'suspend_until'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    public function getConfigAttribute()
    {
        if (isset($this->attributes['config'])) {
            return json_decode($this->attributes['config']);
        } else {
            return null;
        }
    }

    public function setConfigAttribute($value)
    {
        $this->attributes['config'] = (isset($value) ? json_encode((object)$value) : null);
    }
}
