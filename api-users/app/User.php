<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Validation\Rule;
use Sofa\Eloquence\Eloquence;
use CPCommon\Pid\Pid;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, SoftDeletes, Eloquence;

    public static function boot()
    {
        parent::boot();
        static::creating(function (User $user) {
            $user->pid = Pid::create();
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sponsor_id',
        'first_name',
        'last_name',
        'public_id',
        'email',
        'role_id',
        'password',
        'comm_engine_status_id',
        'join_date',
        'phone_number',
    ];

    public static $selects = [
        'users.id as id',
        'users.pid as pid',
        'users.first_name',
        'users.last_name',
        'users.sponsor_id',
        'users.public_id',
        'users.email',
        'users.created_at as created_at',
        'users.updated_at as updated_at',
        'users.phone_number',
        'users.role_id',
    ];

    public static $updateFields = [
        'sponsor_id',
        'first_name',
        'last_name',
        'public_id',
        'email',
        'password',
        'join_date',
        'billing_address',
        'shipping_address',
        'phone_number'
    ];

    protected $casts = [
        'id'          => 'integer',
        'sponsor_id'  => 'integer',
        'enroller_id' => 'integer',
        'role_id'     => 'integer'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'rawStoreSettings',
        'mobile_key',
        'comm_engine_status_id',
        'disabled_at'
    ];

    public static $createRules = [
        'email' => 'required|email|unique:users',
        'first_name' => 'required|max:255',
        'last_name' => 'required|max:255',
        'public_id' => 'required_if:role_id,5|alpha_dash|unique:users|unique:blacklisted,name',
        'plan_pid' => 'required_if:role_id,5|string|exists:subscriptions,pid',
        'password' => 'required_if:role_id,5,7,9',
        'join_date' => 'sometimes|date',
        'role_id' => 'required|in:3,5,7,8',
        'status' => 'sometimes|exists:user_status,name',
    ];

    protected $searchableColumns = [
        'id',
        'first_name',
        'last_name',
        'email'
    ];

    public static $indexRules = [
        'expands' => 'array',
        'expands.*' => 'in:sponsor,subscription,store_settings,settings',
        'start_date' => 'date',
        'end_date' => 'date',
        'date_column' => 'required_with:start_date,end_date|in:created_at,updated_at,join_date',
        'per_page' => 'sometimes|numeric|min:1|max:100',
        'page' => 'sometimes|numeric',
        'sort_by' => 'sometimes|string|in:id,-id,first_name,-first_name,last_name,-last_name,email,-email,role_id,-role_id,join_date,-join_date,created_at,-created_at,updated_at,-updated_at',
        'search_term' => 'sometimes|string',
        'role_id' => 'sometimes|array'
    ];

    public static $findRules = [
        'expands' => 'array',
        'expands.*' => 'in:sponsor,subscription,store_settings,settings',
    ];

    public static function updateRules($userPid)
    {
        return [
            'first_name' => 'sometimes|max:255',
            'last_name' => 'sometimes|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('users')->ignore($userPid, 'pid')
            ],
            'public_id' => [
                'sometimes',
                'required',
                'alpha_dash',
                Rule::unique('users')->ignore($userPid, 'pid'),
                'unique:blacklisted,name',
            ]
        ];
    }

    protected $appends = [
        'role',
        'rawStoreSettings'
    ];

    public function getRoleAttribute()
    {
        switch ($this->role_id) {
            case 3:
                return 'Customer';
            case 5:
                return 'Rep';
            case 7:
                return 'Admin';
            case 8:
                return 'Superadmin';
            default:
                return 'Unknown';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */
    public function sponsor()
    {
        return $this->belongsTo(self::class, 'sponsor_id');
    }

    public function rawStoreSettings()
    {
        return $this->hasMany(StoreSetting::class);
    }

    public function settings()
    {
        return $this->hasOne(Setting::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }
}
