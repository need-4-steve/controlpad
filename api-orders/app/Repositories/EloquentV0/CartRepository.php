<?php

namespace App\Repositories\EloquentV0;

use App\Cart;
use App\Cartline;
use App\CartCoupon;
use CPCommon\Pid\Pid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;

class CartRepository
{

    public function __construct()
    {
        $this->expandsTable = [
            'lines' => function ($query, $params) {
                $query->with(['lines']);
            },
            'coupon' => function ($query, $params) {
                $query->with(['coupon']);
            }
        ];
        $this->paramsTable = [
            'expands' => function ($query, $expands, $params) {
                foreach ($expands as $expand) {
                    try {
                        $this->expandsTable[$expand]($query, $params);
                    } catch (\Exception $e) {
                        dd($e);
                    }
                }
            },
            'buyer_pid' => function ($query, $value, $params) {
                $query->where('buyer_pid', '=', $value);
            },
            'seller_pid' => function ($query, $value, $params) {
                $query->where('seller_pid', '=', $value);
            },
            'type' => function ($query, $value, $params) {
                $query->where('type', '=', $value);
            },
            'inventory_user_pid' => function ($query, $value, $params) {
                $query->where('inventory_user_pid', '=', $value);
            },
            'start_date' => function ($query, $value, $params) {
                $query->where('created_at', '>=', $value);
            },
            'end_date' => function ($query, $value, $params) {
                $query->where('created_at', '<=', $value);
            }
        ];
    }

    public function index($params)
    {
        $perPage = (isset($params['per_page']) ? $params['per_page'] : 25);
        $page = (isset($params['page']) ? $params['page'] : 1);

        $cartQuery = Cart::select('id', 'pid', 'buyer_pid', 'seller_pid', 'inventory_user_pid', 'type', 'coupon_id', 'created_at', 'updated_at');
        $this->getParams($cartQuery, $params);

        return $cartQuery->with('coupon')->simplePaginate($perPage, $page);
    }

    private function getParams($query, $params)
    {
        foreach ($params as $param => $value) {
            try {
                $this->paramsTable[$param]($query, $value, $params);
            } catch (\Exception $e) {
            }
        }
        return $query;
    }

    public function cartByPid($pid, $params = [])
    {
        $cartQuery = Cart::select('id', 'pid', 'buyer_pid', 'seller_pid', 'inventory_user_pid', 'type', 'coupon_id', 'created_at', 'updated_at')
            ->where('pid', $pid);
        $this->getParams($cartQuery, $params);
        return $cartQuery->first();
    }

    public function create($cart)
    {
        $cart = new Cart($cart);
        if (!isset($cart->coupon_id)) {
            $cart->coupon_id = null;
        }
        $cart->pid = Pid::create();
        $cart->save();
        return $cart;
    }

    // TODO figure out a way to add and remove just quantities if existing

    public function addLines($lines)
    {
        Cartline::insert($lines);
    }

    public function removeLineQuantities($orderlines)
    {
        // TODO implement
    }

    public function empty($pid)
    {
        Cartline::whereRaw('cart_id = (select id from carts where pid = ?)', [$pid])->delete();
        Cart::where('pid', '=', $pid)->update(['coupon_id' => null]);
    }

    public function delete($pid)
    {
        Cart::where('pid', '=', $pid)->delete();
    }
}
