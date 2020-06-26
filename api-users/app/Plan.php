<?php

namespace App;

use CPCommon\Pid\Pid;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    public static function boot()
    {
        parent::boot();
        static::creating(function ($plan) {
            $plan->pid = Pid::create();
        });
    }

    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'subscriptions';

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
        'description',
        'duration',
        'free_trial_time',
        'on_sign_up',
        'renewable',
        'seller_type_id',
        'tax_class',
        'title',
        'plan_price',
    ];

    public static $selects = [
        'subscriptions.id',
        'subscriptions.pid',
        'subscriptions.description',
        'subscriptions.duration',
        'subscriptions.free_trial_time',
        'subscriptions.on_sign_up',
        'subscriptions.renewable',
        'subscriptions.seller_type_id',
        'subscriptions.tax_class',
        'subscriptions.title',
        'subscriptions.plan_price',
        'subscriptions.created_at',
        'subscriptions.updated_at',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'slug',
    ];

    public static $indexRules = [
        'per_page' => 'sometimes|numeric|min:1|max:100',
        'page' => 'sometimes|numeric',
        'sort_by' => 'sometimes|string|in:title,-title,plan_price,-plan_price,created_at,-created_at,updated_at,-updated_at',
        'search_term' => 'sometimes|string',
        'sign_up' => 'sometimes|required|in:true,false,1,0,'.true.','.false,
    ];

    public static $createRules = [
        'title' => 'required|string',
        'plan_price' => 'required|numeric',
        'renewable' => 'required|boolean',
        'seller_type_id' => 'required|integer|in:1,2',
        'free_trial_time' => 'integer',
        'on_sign_up' => 'boolean',
        'tax_class' => 'string',
        'description' => 'nullable|string',
    ];

    public static $updateRules = [
        'title' => 'sometimes|required|string',
        'price' => 'sometimes|required|numeric',
        'renewable' => 'sometimes|required|boolean',
        'seller_type_id' => 'sometimes|required|integer|in:1,2',
        'free_trial_time' => 'integer',
        'on_sign_up' => 'boolean',
        'tax_class' => 'string',
        'seller_type_id' => 'sometimes|required|integer|in:1,2',
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
