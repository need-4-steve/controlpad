<?php

namespace App\Repositories\EloquentV0;

use App\Coupon;
use DB;

class CouponRepository
{
    public function index($params)
    {
        $query = Coupon::select('*');

        if (isset($params['owner_pid'])) {
            $query->where('owner_pid', $params['owner_pid']);
        }

        if (isset($params['status'])) {
            switch ($params['status']) {
                case 'used':
                    $query->whereRaw('max_uses <= uses');
                    break;
                case 'active':
                    $query->whereRaw('(expires_at IS NULL OR expires_at > CURRENT_TIMESTAMP)');
                    $query->whereRaw('max_uses > uses');
                    break;
                case 'expired':
                    $query->whereRaw('expires_at < CURRENT_TIMESTAMP');
                    break;
            }
        }
        if (!empty($params['search_term'])) {
            $query->where(function ($subQuery) use ($params) {
                $term = "%".$params['search_term']."%";
                $subQuery->where('title', 'LIKE', $term)
                    ->orWhere('code', 'LIKE', $term)
                    ->orWhere('amount', 'LIKE', $term)
                    ->orWhere('description', 'LIKE', $term)
                    ->orWhere('max_uses', 'LIKE', $term);
            });
        }
        if (isset($params['type'])) {
            $query->where('type', $params['type']);
        }
        if (!empty($params['sort_by'])) {
            $query->orderBy($params['sort_by'], $params['in_order']);
        }
        if (empty($params['per_page'])) {
            $params['per_page'] = 25;
        }
        if (empty($params['page'])) {
            $params['page'] = 1;
        }
        return $query->paginate($params['per_page'], $params['page']);
    }

    public function couponById($id)
    {
        return Coupon::where('id', $id)->first();
    }

    public function couponByCode($code, $ownerPid)
    {
        return Coupon::where('code', $code)->where('owner_pid', '=', $ownerPid)->first();
    }

    public function create($coupon)
    {
        $coupon['uses'] = 0;
        return Coupon::create($coupon);
    }

    public function isCodeAvailable($code, $ownerPid)
    {
        return !Coupon::where('code', $code)->where('owner_pid', $ownerPid)->exists();
    }

    public function addUse($id)
    {
        // Only update uses if not maxed out, return result to see if it worked
        return (app('db')->update('update coupons set uses = uses + 1 where id = ? and uses < max_uses', [$id]) > 0);
    }

    public function subtractUse($id)
    {
        return (app('db')->update('update coupons set uses = uses - 1 where id = ?', [$id]) > 0);
    }

    public function addUseUnsafe($id)
    {
        app('db')->update('update coupons set uses = uses + 1 where id = ?', [$id]);
    }

    public function isCouponAvailable($id)
    {
        return Coupon::selectRaw('(uses < max_uses) as available')->where('id', '=', $id)->value('available');
    }
}
