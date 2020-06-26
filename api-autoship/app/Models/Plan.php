<?php

namespace App\Models;

class Plan extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'autoship_plans';

    /**
     * The attributes that are mass assignable
     *
     * @var array
     */
    protected $fillable = [
        'description',
        'disabled_at',
        'discounts',
        'duration',
        'free_shipping',
        'frequency',
        'pid',
        'title',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'id',
    ];

    protected $casts = [
        'discounts' => 'object',
        'free_shipping' => 'boolean',
    ];

    /**
     * The rules to apply for validation.
     *
     * @var array
     */
    public static $rules = [
        'description'               => 'string|max:255|nullable',
        'disable'                   => 'sometimes|required|in:true,false,1,0,'.true.','.false,
        'duration'                  => 'required|in:Days,Weeks,Months,Quarters,Years',
        'free_shipping'             => 'sometimes|required|in:true,false,1,0,'.true.','.false,
        'frequency'                 => 'required|integer',
        'title'                     => 'string|nullable',
        'visibilities'              => 'array',
        'visibilities.*'            => 'array',
        'visibilities.*.id'         => 'required_with:visibilities.*|integer',
        'discounts'                 => 'required|array',
        'discounts.*'               => 'required|array',
        'discounts.*.min_quantity'  => 'required_with:discounts|integer|min:1',
        'discounts.*.percent'       => 'required_with:discounts|numeric',
    ];

    /**
     * The columns to return from the database.
     *
     * @var array
     */
    public static $selects = [
        'autoship_plans.id',
        'autoship_plans.pid',
        'autoship_plans.created_at',
        'autoship_plans.description',
        'autoship_plans.disabled_at',
        'autoship_plans.discounts',
        'autoship_plans.duration',
        'autoship_plans.free_shipping',
        'autoship_plans.frequency',
        'autoship_plans.title',
        'autoship_plans.updated_at',
    ];

    protected $searchableColumns = [
        'pid' => 5,
        'description' => 3,
        'duration' => 1,
        'title' => 4,
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */

    public function visibilities()
    {
        return $this->belongsToMany(Visibility::class, 'autoship_plan_visibility', 'autoship_plan_id');
    }
}
