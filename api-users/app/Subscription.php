<?php

namespace App;

use CPCommon\Pid\Pid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use SoftDeletes;

    public static function boot()
    {
        parent::boot();
        static::creating(function ($subscription) {
            $subscription->pid = Pid::create();
        });
    }

    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'subscription_user';

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
        'user_pid',
        'ends_at',
        'auto_renew',
        'fail_description',
        'ends_at',
        'created_at',
        'updated_at',
        'user_id',
        'subscription_price',
        'subscription_id',
    ];

    public static $selects = [
        'subscription_user.pid',
        'subscription_user.user_pid',
        'subscription_user.ends_at',
        'subscription_user.auto_renew',
        'subscription_user.fail_description',
        'subscription_user.ends_at',
        'subscription_user.created_at',
        'subscription_user.updated_at',
        'subscription_user.user_id',
        'subscription_user.subscription_price',
        'users.first_name',
        'users.last_name',
        'users.seller_type_id',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'user_id',
        'subscription_id',
    ];


    public static $indexRules = [
        'start_date' => 'date',
        'end_date' => 'date',
        'date_column' => 'required_with:start_date,end_date|in:created_at,updated_at,ends_at',
        'per_page' => 'sometimes|numeric|min:1|max:100',
        'page' => 'sometimes|numeric',
        'sort_by' => 'sometimes|string|in:title,-title,price,-price,created_at,-created_at,updated_at,-updated_at,ends_at,-ends_at',
        'search_term'  => 'sometimes|string',
        'seller_type_id' => 'in:1,2',
    ];

    public static $updateRules = [
        'subscription_price' => 'sometimes|required|numeric',
        'ends_at' => 'sometimes|required|date',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */
}
