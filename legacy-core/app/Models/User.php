<?php

namespace App\Models;

use App\Models\Traits\EnabledTrait;
use App\Models\Traits\HistoryTrait;
use App\Models\TermsAcceptance;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Sofa\Eloquence\Eloquence;
use Illuminate\Foundation\Auth\User as Authenticatable;
use CPCommon\Pid\Pid;

class User extends Authenticatable
{
    use EnabledTrait;
    use HistoryTrait;
    use SoftDeletes;
    use Notifiable;
    use Eloquence;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

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
        'id',
        'sponsor_id',
        'enroller_id',
        'first_name',
        'last_name',
        'public_id',
        'email',
        'gender',
        'dob',
        'country_of_residence',
        'role_id',
        'deleted_at',
        'disabled_at',
        'password',
        'seller_type_id',
        'pid',
        'comm_engine_status_id',
        'join_date',
        'phone_number'
    ];

    /**
     * The attributes that should be cast to native types
     * E.g. always cast a number as integer instead
     * of a string
     *
     * @var array
     */
    protected $casts = [
        'id'          => 'integer',
        'sponsor_id'  => 'integer',
        'enroller_id' => 'integer',
        'role_id'     => 'integer'
    ];

    /**
     * The attributes that should be mutated to dates
     * E.g. deleted_at, published_at, etc
     *
     * @var array
     */
    protected $dates = [
        'deleted_at',
        'disabled_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token'
    ];

    /**
     * Additional attributes
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * The accessors to append to the model's array form
     *
     * @var array
     */
    protected $appends = [
        'full_name',
     ];

    /**
     * Validation rules.
     * @var array
     */
    public static $rules = [
       'email' => 'required|email|unique:users',
       'first_name' => 'required',
       'last_name' => 'required',
       'password' => 'required|min:8|confirmed',
       'public_id' => 'sometimes|required|unique:users',
       'number' => 'required|min:10|max:11'
    ];

    /**
     * The relations to eager load on every query.
     * Use conservatively.
     *
     * @var array
     */
    protected $with = [
        'role'
    ];

    protected $searchableColumns = [
        'id',
        'first_name',
        'last_name',
    ];

    /*
     |--------------------------------------------------------------------------
     | Events
     |--------------------------------------------------------------------------
     */
    public static function boot()
    {
        parent::boot();
        static::creating(function (User $user) {
            $user->pid = Pid::create();
        });
        self::created(function ($model) {
            TermsAcceptance::firstOrCreate(['user_id' => $model->id]);
        });
    }

    /*
     |--------------------------------------------------------------------------
     | Accessors and Mutators
     |--------------------------------------------------------------------------
     |
     | These are methods used to alter existing properties before returning
     | them or to create pseudo-properties for the model.
     |
     */

    public function getFullNameAttribute()
    {
        return $this->attributes['first_name'].' '.$this->attributes['last_name'];
    }

    public function hasRole($roles)
    {
        $roleName = session()->get('auth-role-'.$this->attributes['id']);
        if (!$roleName) {
            $roleName = $this->role()->first()->name;
            session()->put('auth-role-'.$this->attributes['id'], $roleName);
        }
        if (is_array($roles)) {
            return (in_array($roleName, $roles, true));
        }

        return ($roleName === $roles);
    }

    public function hasSellerType($sellerTypes)
    {
        $sellerType = session()->get('seller-type-'.$this->attributes['id']);
        if (!$sellerType) {
            $sellerType = $this->sellerType()->first();
            session()->put('seller-type-'.$this->attributes['id'], $sellerType);
        }
        if (!$sellerType) {
            return false;
        }
        $sellerTypeName = $sellerType->name;
        if (is_array($sellerTypes)) {
            return (in_array($sellerTypeName, $sellerTypes, true));
        }
        return ($sellerTypeName === $sellerTypes);
    }

    public function getRememberToken()
    {
        return $this->attributes['remember_token'];
    }

    public function setRememberToken($value)
    {
        $this->attributes['remember_token'] = $value;
    }

    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    public function lastSubscription()
    {
        $subscriptions = $this->subscriptions();
        if (count($subscriptions) > 0) {
            $subscriptions->orderBy('ends_at', 'DESC')->first();
        }
        return $subscriptions;
    }

    /**
     * Return oauth token based on a driver name. This is here
     * to allow this method to be called on auth()->user()
     *
     * @param $driverName All lowercase string of the driver name, e.g. facebook
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function hasToken($driverName)
    {
        return $this->oauthTokens()->whereDriver($driverName)->first();
    }

    /*
    |------------------------------------------------------------------------
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

    public function addresses()
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    public function billingAddress()
    {
        return $this->morphOne(Address::class, 'addressable')->where('label', 'Billing');
    }

    public function shippingAddress()
    {
        return $this->morphOne(Address::class, 'addressable')->where('label', 'Shipping');
    }

    public function businessAddress()
    {
        return $this->morphOne(Address::class, 'addressable')->where('label', 'Business');
    }

    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    public function cart()
    {
        return $this->hasOne('Cart');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_header')->withPivot('header');
    }

    public function coupons()
    {
        return $this->hasMany(Coupon::class, 'owner_id', 'id');
    }

    public function companyInfo()
    {
        return $this->hasOne(CompanyInfo::class);
    }

    public function customers()
    {
        return $this->belongsToMany(User::class, 'customers', 'user_id', 'customer_id');
    }

    public function history()
    {
        return $this->hasMany(History::class);
    }

    public function items()
    {
        return $this->belongsToMany(Item::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function notes()
    {
        return $this->morphMany(Note::class, 'noteable');
    }

    public function oauthTokens()
    {
        return $this->hasMany(OauthToken::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function payQuickerToken()
    {
        return $this->hasOne(PayQuickerToken::class);
    }

    public function phone()
    {
        return $this->morphOne(Phone::class, 'phonable');
    }

    public function profileImage()
    {
        return $this->morphToMany(Media::class, 'mediable');
    }

    public function settings()
    {
        return $this->hasOne(UserSetting::class);
    }

    public function shipRates()
    {
        return $this->hasMany(ShippingRate::class);
    }

    public function sponsor()
    {
        return $this->belongsTo(self::class, 'sponsor_id');
    }

    public function storeSettings()
    {
        return $this->hasMany(StoreSetting::class);
    }

    public function subscriptions()
    {
        return $this->hasOne(SubscriptionUser::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function sellerType()
    {
        return $this->belongsTo(SellerType::class);
    }

    public function cardToken()
    {
        return $this->hasOne(CardToken::class);
    }

    public function termsAccepted()
    {
        return $this->hasOne(TermsAcceptance::class);
    }
}
