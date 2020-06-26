<?php

namespace App\Repositories\Eloquent;

use Carbon\Carbon;
use App\Models\Coupon;
use App\Models\User;
use App\Repositories\Contracts\CouponRepositoryContract;
use App\Repositories\Eloquent\Traits\CommonCrudTrait;
use App\Repositories\Eloquent\AuthRepository;
use App\Repositories\Eloquent\UserSettingsRepository;

class CouponRepository implements CouponRepositoryContract
{
    use CommonCrudTrait;


    /**
     * Constructor
     */
    public function __construct(AuthRepository $authRepo)
    {
        $this->authRepo = $authRepo;
    }

    /**
     * Get auth based index
     */
    public function index($request, $paginate = true)
    {
        $coupons = Coupon::where('owner_id', $this->authRepo->getOwnerId());
        $now = Carbon::now()->toDateTimeString();

        if ($request['status'] === 'used') {
            $coupons = $coupons->whereRaw('max_uses = uses');
        }
        if ($request['status'] === 'active') {
            $coupons = $coupons
                ->where(function ($query) use ($now) {
                    $query->where('expires_at', '>', $now)
                        ->orWhere('expires_at', null);
                })
                ->where('owner_id', $this->authRepo->getOwnerId())
                ->whereRaw('max_uses > uses');
        }
        if ($request['status'] === 'expired') {
            $coupons = $coupons->where('expires_at', '<', $now);
        }
        if (!empty($request['search_term'])) {
            $coupons = $coupons
                ->where(function ($query) use ($request) {
                    $query->where('title', 'LIKE', "%" . $request['search_term'] . "%")
                        ->orWhere('code', 'LIKE', "%" . $request['search_term'] . "%")
                        ->orWhere('amount', 'LIKE', "%" . $request['search_term'] . "%")
                        ->orWhere('description', 'LIKE', "%" . $request['search_term'] . "%")
                        ->orWhere('max_uses', 'LIKE', "%" . $request['search_term'] . "%");
                });
        }
        if (isset($request['expands']) && in_array('orders', $request['expands'])) {
            $coupons->with('orders.customer');
        }
        if (empty($request['order'])) {
            $request['order'] = 'DESC';
        }
        if (!empty($request['column'])) {
            $coupons = $coupons->orderBy($request['column'], $request['order']);
        } else {
            $coupons = $coupons->orderBy('updated_at', 'DESC');
        }

        if (!empty($request['per_page']) and is_numeric($request['per_page']) and $paginate) {
            return $coupons->paginate($request['per_page']);
        }
        return $coupons->get();
    }

    /**
     * Find a coupon for a user by the coupon code
     *
     * @param $code
     * @param $userId
     * @return mixed
     */
    public function findByCodeForUser($code, $userId)
    {
        return Coupon::where(['code' => $code, 'owner_id' => $userId])->first();
    }

    /**
     * Create a new instance of Coupon
     *
     * @param array $inputs
     * @return bool|Coupon
     */
    public function create(array $inputs = [])
    {
        $coupon = new Coupon;

        if (isset($inputs['can_expire']) && $inputs['can_expire']) {
            $inputs['expires_at'] = Carbon::parse($inputs['expires_at'])->endOfDay()->toDateTimeString();
        } else {
            $inputs['expires_at'] = null;
        }

        $fields = ['code', 'owner_id', 'amount', 'is_percent', 'title', 'description', 'max_uses', 'expires_at', 'type'];
        foreach ($fields as $field) {
            $coupon->$field = array_get($inputs, $field);
        }

        // check to see if we have uses set as something other than default
        if (isset($inputs['uses'])) {
            $coupon->uses = $inputs['uses'];
        }

        $owner = User::select('pid')->where('id', $coupon->owner_id)->first();
        if (isset($owner->pid)) {
            $coupon->owner_pid = $owner->pid;
        }
        if (! $coupon->save()) {
            return false;
        }

        return $coupon;
    }

    public function showCoupon($coupon_id)
    {
        $coupon = Coupon::where('id', $coupon_id)->with('orders')->first();
        return $coupon->orders()->paginate(20);
    }
}
