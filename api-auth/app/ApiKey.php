<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'secret',
        'app_id',
        'app_name',
        'tenant_id',
        'expires_at'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'secret'
    ];

    public static $updateRules = [
        'app_name' => 'sometimes',
        'refresh' => 'sometimes|boolean',
        'services' => 'sometimes|array',
        'services.*' => 'integer'
    ];

    public static $updateFields = [
        'app_name'
    ];

    public function keyServices()
    {
        return $this->belongsToMany('App\Service', 'key_services', 'api_key', 'service_id');
    }

}
