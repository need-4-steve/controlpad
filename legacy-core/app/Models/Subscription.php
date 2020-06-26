<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HistoryTrait;
use CPCommon\Pid\Pid;

class Subscription extends Model
{
    use HistoryTrait;

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->pid = Pid::create();
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
     * The attributes that are mass assignable
     *
     * @var array
     */
    protected $fillable = [
        'plan_price',
        'title',
        'slug',
        'duration',
        'renewable',
        'free_trial_time',
        'description',
        'seller_type_id',
        'on_sign_up',
        'tax_class',
        'pid'
    ];

    public static $rules = [
        'title' => 'required',
        'price' => 'required',
        'renewable' => 'required',
        'seller_type_id' => 'required|integer',
        'free_trial_time' => 'required',
        'on_sign_up' => 'required',
        'tax_class' => 'sometimes|size:8'
     ];

    /**
     * The attributes that should be cast to native types
     * E.g. always cast a number as integer instead
     * of a string
     *
     * @var array
     */
    protected $casts = [
        'duration' => 'integer',
        'price'    => 'double',
        'description' => 'string',

    ];

    /**
     * The attributes that should be mutated to dates
     * E.g. deleted_at, published_at, etc
     *
     * @var array
     */
    protected $dates = [];

    /**
     * The attributes that should be hidden for arrays
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Additional attributes
     *
     * @var array
     */
    protected $attributes = [];


    /**
     * The relations to eager load on every query.
     * Use conservatively.
     *
     * @var array
     */
    protected $with = [];

    /*
    |--------------------------------------------------------------------------
    | Accessors and Mutators
    |--------------------------------------------------------------------------
    |
    | These are methods used to alter existing properties before returning
    | them or to create pseudo-properties for the model.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    | These are methods that are an alias to more complicated operations
    | to a simple Eloquent method for the model.
    |
    */

    //

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you specify relations between this model and other models.
    |
    */

    public function price()
    {
        return $this->morphOne(Price::class, 'priceable');
    }
}
