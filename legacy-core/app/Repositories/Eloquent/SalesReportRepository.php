<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\UserSettingsRepository;
use App\Models\User;
use App\Models\Order;
use Carbon\Carbon;

class SalesReportRepository
{
    public function __construct(
        OrderRepository $orderRepo,
        UserSettingsRepository $userSettingsRepo
    ) {
        $this->orderRepo = $orderRepo;
        $this->userSettingsRepo = $userSettingsRepo;
    }

    public function getTaxTotal($request = null, $orderType = null)
    {
        $request = $this->orderRepo->prepareOrderRequest($request);
        switch ($orderType) {
            case 'corp':
                $orderTypes = [1, 2, 5, 8];
                break;
            case 'rep':
                $orderTypes = [3, 4];
                break;
            case 'fbc':
                $orderTypes = [6, 7];
                break;
            default:
                $orderTypes = [1, 2, 3, 4, 5, 6, 7, 8];
        }
        $timezone = $this->userSettingsRepo->getUserTimeZone(config('site.apex_user_id'));
        $request['start_date'] = Carbon::parse($request['start_date'], $timezone)->startOfDay()->setTimezone('UTC')->format('Y-m-d H:i:s');
        $request['end_date'] = Carbon::parse($request['end_date'], $timezone)->endOfDay()->setTimezone('UTC')->format('Y-m-d H:i:s');
        $total = Order::whereIn('orders.type_id', $orderTypes)
            ->whereBetween('orders.created_at', [$request['start_date'], $request['end_date']])
            ->search($request['search_term'], ['customer.first_name', 'customer.last_name', 'receipt_id'])
            ->sum('total_tax', ['customer.first_name', 'customer.last_name', 'receipt_id']);
        return isset($total) ? $total : 0;
    }

    public function getRepsIndex($request = null)
    {
        $request = $this->orderRepo->prepareOrderRequest($request, $paginate = true);
        if ($request['column'] === 'id') {
            $request['column'] = 'users.id';
        }

        $timezone = $this->userSettingsRepo->getUserTimeZone(config('site.apex_user_id'));
        $request['start_date'] = Carbon::parse($request['start_date'], $timezone)->startOfDay()->setTimezone('UTC')->format('Y-m-d H:i:s');
        $request['end_date'] = Carbon::parse($request['end_date'], $timezone)->endOfDay()->setTimezone('UTC')->format('Y-m-d H:i:s');
        $users = User::selectRaw('first_name, last_name, role_id, users.id as id, sum(orders.total_price) as retail_total')
            ->join('orders', function ($join) use ($request) {
                $join->on('orders.store_owner_user_id', '=', 'users.id')
                ->where('orders.type_id', 3)
                ->whereBetween('orders.created_at', [$request['start_date'], $request['end_date']]);
            })
            ->search($request['search_term'], ['first_name', 'last_name'])
            ->groupBy('users.id')
            ->orderBy($request['column'], $request['order']);
        if ($paginate) {
            return $users->paginate($request['per_page']);
        } else {
             return $users->get();
        }
    }

    public function getRepTotal($request = null)
    {
        $request = $this->orderRepo->prepareOrderRequest($request);
        if ($request['column'] === 'id') {
            $request['column'] = 'users.id';
        }
        $timezone = $this->userSettingsRepo->getUserTimeZone(config('site.apex_user_id'));
        $request['start_date'] = Carbon::parse($request['start_date'], $timezone)
            ->startOfDay()->setTimezone('UTC')->format('Y-m-d H:i:s');
        $request['end_date'] = Carbon::parse($request['end_date'], $timezone)
            ->endOfDay()->setTimezone('UTC')->format('Y-m-d H:i:s');

        $repTotal = User::selectRaw('first_name, last_name, role_id, users.id as id, sum(orders.total_price) as retail_total')
            ->join('orders', function ($join) use ($request) {
                $join->on('orders.store_owner_user_id', '=', 'users.id')
                ->where('orders.type_id', 3)
                ->whereBetween('orders.created_at', [$request['start_date'], $request['end_date']]);
            })
            ->search($request['search_term'], ['first_name', 'last_name'])
            ->orderBy($request['column'], $request['order'])
            ->sum('total_price');
        return isset($repTotal) ? $repTotal : 0;
    }

    public function getUserIndex($request = null, $userId = null, $orderType = null, $paginate = true)
    {
        if (isset($request['name'])) {
            if ($request['name'] === 'customer' && $request['column'] === 'last_name') {
                $request['column'] = 'customer_last_name';
            } elseif ($request['name'] === 'rep' && $request['column'] === 'last_name') {
                $request['column'] = 'owner_last_name';
            }
        }
        if (auth()->user()->hasRole(['Admin', 'Superadmin']) and $userId === null) {
            switch ($orderType) {
                case 'all':
                    $orderTypes = [1, 2, 3, 4, 5, 6, 7, 8];
                    break;
                case 'wholesale':
                    $orderTypes = [1];
                    break;
                case 'retail':
                    $orderTypes = [2];
                    break;
                case 'rep':
                    $orderTypes = [3,4];
                    break;
                case 'rep-transfer':
                    $orderTypes = [11];
                    break;
                default:
                    $orderTypes = [1, 2];
            }
        } else {
            $orderTypes = [3];
        }

        $timezone = $this->userSettingsRepo->getUserTimeZone(config('site.apex_user_id'));
        $request['start_date'] = Carbon::parse($request['start_date'], $timezone)->startOfDay()->setTimezone('UTC')->format('Y-m-d H:i:s');
        $request['end_date'] = Carbon::parse($request['end_date'], $timezone)->endOfDay()->setTimezone('UTC')->format('Y-m-d H:i:s');

        $request = $this->orderRepo->prepareOrderRequest($request);


        $orders = Order::with('orderType')
            ->whereIn('type_id', $orderTypes)
            ->join('users', 'users.id', '=', 'orders.customer_id')
            ->join('users as store_owner', 'users.sponsor_id', '=', 'store_owner.id')
            ->select('orders.*', 'users.first_name as customer_first_name', 'users.last_name as customer_last_name')
            ->whereBetween('orders.created_at', [$request['start_date'], $request['end_date']]);

        if (isset($request['column'])) {
            if ($request['column'] === 'owner_last_name') {
                $orders->search($request['search_term'], ['customer.first_name', 'customer.last_name', 'receipt_id']);
            } elseif ($request['column'] === 'customer_last_name') {
                $orders->search($request['search_term'], ['customer.first_name', 'customer.last_name', 'receipt_id']);
            } else {
                $orders->orderBy($request['column'], $request['order']);
            }
        }

        if (isset($userId)) {
            $orders->where('store_owner_user_id', $userId);
        }
        if ($paginate) {
            return $orders->paginate($request['per_page']);
        } else {
            $orders = $orders->get();
            foreach ($orders as $order) {
                $order->updated_at = Carbon::parse($order->updated_at, 'UTC')->setTimezone($timezone)->toDateTimeString();
                $order->created_at = Carbon::parse($order->created_at, 'UTC')->setTimezone($timezone)->toDateTimeString();
            }
            return $orders;
        }
    }

    public function getCustReportIndex($request = null, $userId = null, $orderType = null, $paginate = true)
    {
        if (isset($request['name'])) {
            if ($request['name'] === 'customer' && $request['column'] === 'last_name') {
                $request['column'] = 'customer_last_name';
            } elseif ($request['name'] === 'rep' && $request['column'] === 'last_name') {
                $request['column'] = 'owner_last_name';
            }
        }
        if (auth()->user()->hasRole(['Admin', 'Superadmin']) and $userId === null) {
            switch ($orderType) {
                case 'all':
                    $orderTypes = [1, 2, 3, 4, 5, 6, 7, 8];
                    break;
                case 'wholesale':
                    $orderTypes = [1];
                    break;
                case 'retail':
                    $orderTypes = [2];
                    break;
                case 'rep':
                    $orderTypes = [3,4];
                    break;
                default:
                    $orderTypes = [1, 2];
            }
        } else {
            $orderTypes = [3];
        }

        $timezone = $this->userSettingsRepo->getUserTimeZone(config('site.apex_user_id'));
        $request['start_date'] = Carbon::parse($request['start_date'], $timezone)->startOfDay()->setTimezone('UTC')->format('Y-m-d H:i:s');
        $request['end_date'] = Carbon::parse($request['end_date'], $timezone)->endOfDay()->setTimezone('UTC')->format('Y-m-d H:i:s');

        $request = $this->orderRepo->prepareOrderRequest($request);

        $cust_report = Order::with('orderType')
            ->whereIn('type_id', [2, 3])
            ->join('users', 'users.id', '=', 'orders.customer_id')
            ->join('users as store_owner', 'users.sponsor_id', '=', 'store_owner.id')
            ->select('orders.*', 'users.sponsor_id', 'users.first_name as customer_first_name', 'users.last_name as customer_last_name', \DB::raw('CONCAT(store_owner.last_name, ", ", store_owner.first_name) AS sponsorname'))
            ->whereBetween('orders.created_at', [$request['start_date'], $request['end_date']]);

        if (isset($request['column']) && $request['column'] === 'owner_last_name') {
            $cust_report->search($request['search_term'], ['customer.first_name', 'customer.last_name', 'receipt_id']);
        } else {
            $cust_report->search($request['search_term'], ['customer.first_name', 'customer.last_name', 'receipt_id']);
        }
            $cust_report->orderBy($request['column'], $request['order']);
        if (isset($userId)) {
            $cust_report->where('store_owner_user_id', $userId);
        }
        if ($paginate) {
            return $cust_report->paginate($request['per_page']);
        } else {
            $cust_report = $cust_report->get();
            foreach ($cust_report as $cust_reports) {
                $cust_reports->updated_at = Carbon::parse($cust_reports->updated_at, 'UTC')->setTimezone($timezone)->toDateTimeString();
                $cust_reports->created_at = Carbon::parse($cust_reports->created_at, 'UTC')->setTimezone($timezone)->toDateTimeString();
            }
            return $cust_report;
        }
    }

    public function getUserTotal($request = null, $userId = null, $orderType = null)
    {
        if (auth()->user()->hasRole(['Admin', 'Superadmin']) and $userId === null) {
            switch ($orderType) {
                case 'all':
                    $orderTypes = [1, 2, 3, 4, 5, 6, 7, 8];
                    break;
                case 'wholesale':
                    $orderTypes = [1];
                    break;
                case 'retail':
                    $orderTypes = [2];
                    break;
                case 'rep-transfer':
                    $orderTypes = [11];
                    break;
                default:
                    $orderTypes = [1, 2];
            }
        } else {
            $orderTypes = [3];
        }

            $timezone = $this->userSettingsRepo->getUserTimeZone(config('site.apex_user_id'));
            $request['start_date'] = Carbon::parse($request['start_date'], $timezone)
                ->startOfDay()->setTimezone('UTC')->format('Y-m-d H:i:s');
            $request['end_date'] = Carbon::parse($request['end_date'], $timezone)->endOfDay()
                ->setTimezone('UTC')->format('Y-m-d H:i:s');

        $request = $this->orderRepo->prepareOrderRequest($request);
        $total = Order::whereIn('orders.type_id', $orderTypes)
            ->whereBetween('orders.created_at', [$request['start_date'], $request['end_date']])
            ->search($request['search_term'], ['customer.first_name', 'customer.last_name', 'receipt_id']);
        if (isset($userId)) {
            $total->where('store_owner_user_id', $userId);
        }
        $userTotal = $total->sum('total_price');
        return isset($userTotal) ? $userTotal : 0;
    }

    public function getFbcIndex($request = null)
    {
        $timezone = $this->userSettingsRepo->getUserTimeZone(config('site.apex_user_id'));
        $request['start_date'] = Carbon::parse($request['start_date'], $timezone)
            ->startOfDay()->setTimezone('UTC')->format('Y-m-d H:i:s');
        $request['end_date'] = Carbon::parse($request['end_date'], $timezone)->endOfDay()
            ->setTimezone('UTC')->format('Y-m-d H:i:s');

        $request = $this->orderRepo->prepareOrderRequest($request);
        $users = User::selectRaw('first_name, last_name, role_id, users.id as id, sum(orderlines.price * orderlines.quantity) as fbc_total, orders.created_at as created_at')
            ->whereBetween('orders.created_at', [$request['start_date'], $request['end_date']])
            ->join('orderlines', function ($join) {
                $join->on('inventory_owner_id', '=', 'users.id')
                ->where('users.id', '!=', config('site.apex_user_id'));
            })
            ->join('orders', function ($join) {
                $join->on('orders.id', '=', 'orderlines.order_id')
                ->whereIn('orders.type_id', [6, 7]);
            })
            ->whereRaw('Concat(first_name, " ",last_name) LIKE "%'.$request['search_term'].'%"')
            ->groupBy('users.id')
            ->orderBy($request['column'], $request['order'])
            ->paginate($request['per_page']);
        return $users;
    }

    public function getFbcUser($request = null, $userId = null, $paginate = true)
    {
        $timezone = $this->userSettingsRepo->getUserTimeZone(config('site.apex_user_id'));
        $request['start_date'] = Carbon::parse($request['start_date'], $timezone)
            ->startOfDay()->setTimezone('UTC')->format('Y-m-d H:i:s');
        $request['end_date'] = Carbon::parse($request['end_date'], $timezone)->endOfDay()
            ->setTimezone('UTC')->format('Y-m-d H:i:s');
        $request = $this->orderRepo->prepareOrderRequest($request);
        $orders = Order::with('orderType')
            ->whereIn('type_id', [6, 7])
            ->select('orders.*', 'first_name as customer_first_name', 'last_name as customer_last_name')
            ->join('users', 'users.id', '=', 'customer_id')
            ->whereBetween('orders.created_at', [$request['start_date'], $request['end_date']]);
        if ($userId) {
                $orders->whereHas('lines', function ($query) use ($userId) {
                    $query->where('inventory_owner_id', $userId);
                })
                ->with(['lines' => function ($query) use ($userId) {
                    $query->where('inventory_owner_id', $userId);
                }]);
        } else {
            $orders->whereHas('lines', function ($query) {
                $query->where('inventory_owner_id', '!=', config('site.apex_user_id'));
            })
            ->with(['lines' => function ($query) {
                $query->where('inventory_owner_id', '!=', config('site.apex_user_id'))
                    ->with('owner');
            }]);
        }
            $orders->search($request['search_term'], ['lines.owner.first_name', 'lines.owner.last_name'])
            ->orderBy($request['column'], $request['order']);
        if ($paginate) {
            return $orders->paginate($request['per_page']);
        } else {
            $orders = $orders->get();
            foreach ($orders as $order) {
                $order->updated_at = Carbon::parse($order->updated_at, 'UTC')->setTimezone($timezone)->toDateTimeString();
                $order->created_at = Carbon::parse($order->created_at, 'UTC')->setTimezone($timezone)->toDateTimeString();
            }
            return $orders;
        }
    }

    public function getFbcTotal($request = null, $userId = null)
    {
        $timezone = $this->userSettingsRepo->getUserTimeZone(config('site.apex_user_id'));
        $request['start_date'] = Carbon::parse($request['start_date'], $timezone)
            ->startOfDay()->setTimezone('UTC')->format('Y-m-d H:i:s');
        $request['end_date'] = Carbon::parse($request['end_date'], $timezone)->endOfDay()
            ->setTimezone('UTC')->format('Y-m-d H:i:s');
        $request = $this->orderRepo->prepareOrderRequest($request);
        $total = Order::selectRaw('sum(orderlines.price * orderlines.quantity) as fbc_total')
            ->whereIn('orders.type_id', [6, 7])
            ->whereBetween('orders.created_at', [$request['start_date'], $request['end_date']])
            ->join('orderlines', function ($join) use ($userId) {
                $join->on('orders.id', '=', 'orderlines.order_id')
                ->where('orderlines.inventory_owner_id', '!=', config('site.apex_user_id'));
                if (isset($userId)) {
                    $join->where('orderlines.inventory_owner_id', '=', $userId);
                }
            })
            ->rightJoin('users', function ($join) use ($request) {
                $join->on('users.id', '=', 'inventory_owner_id')
                ->whereRaw('Concat(first_name, " ",last_name) LIKE "%'.$request['search_term'].'%"');
            });
        $total = $total->first();
        $fbcTotal = $total->fbc_total;
        return isset($fbcTotal) ? $fbcTotal : 0;
    }

    public function getAffiliateIndex($request = null, $paginate = true)
    {
        $timezone = $this->userSettingsRepo->getUserTimeZone(config('site.apex_user_id'));
        $request['start_date'] = Carbon::parse($request['start_date'], $timezone)
            ->startOfDay()->setTimezone('UTC')->format('Y-m-d H:i:s');
        $request['end_date'] = Carbon::parse($request['end_date'], $timezone)->endOfDay()
            ->setTimezone('UTC')->format('Y-m-d H:i:s');
        $request = $this->orderRepo->prepareOrderRequest($request, $paginate);
        if ($request['column'] === 'id') {
            $request['column'] = 'users.id';
        }
        $users = User::selectRaw('first_name, last_name, role_id, users.id as id, sum(orders.total_price) as retail_total')
            ->join('orders', function ($join) use ($request) {
                $join->on('orders.store_owner_user_id', '=', 'users.id')
                ->where('orders.type_id', 9)
                ->whereBetween('orders.created_at', [$request['start_date'], $request['end_date']]);
            })
            ->search($request['search_term'], ['first_name', 'last_name'])
            ->groupBy('users.id')
            ->orderBy($request['column'], $request['order']);
        if ($paginate) {
            return $users->paginate($request['per_page']);
        } else {
             return $users->get();
        }
    }

    public function getAffiliateIndexTotal($request = null, $userId = null)
    {
        $timezone = $this->userSettingsRepo->getUserTimeZone(config('site.apex_user_id'));
        $request['start_date'] = Carbon::parse($request['start_date'], $timezone)
            ->startOfDay()->setTimezone('UTC')->format('Y-m-d H:i:s');
        $request['end_date'] = Carbon::parse($request['end_date'], $timezone)->endOfDay()
            ->setTimezone('UTC')->format('Y-m-d H:i:s');
        $request = $this->orderRepo->prepareOrderRequest($request);
        if ($request['column'] === 'id') {
            $request['column'] = 'users.id';
        }
        $repTotal = User::selectRaw('first_name, last_name, role_id, users.id as id, sum(orders.total_price) as retail_total')
            ->join('orders', function ($join) use ($request) {
                $join->on('orders.store_owner_user_id', '=', 'users.id')
                ->where('orders.type_id', 9)
                ->whereBetween('orders.created_at', [$request['start_date'], $request['end_date']]);
            })
            ->search($request['search_term'], ['first_name', 'last_name'])
            ->orderBy($request['column'], $request['order'])
            ->sum('total_price');
        return isset($repTotal) ? $repTotal : 0;
    }

    public function getAffiliateUser($request, $userId = null, $paginate = true)
    {
        if (isset($request['name'])) {
            if ($request['name'] === 'affiliate' && $request['column'] === 'last_name') {
                $request['column'] = 'owner_last_name';
            }
        }
        $timezone = $this->userSettingsRepo->getUserTimeZone(config('site.apex_user_id'));
        $request['start_date'] = Carbon::parse($request['start_date'], $timezone)
            ->startOfDay()->setTimezone('UTC')->format('Y-m-d H:i:s');
        $request['end_date'] = Carbon::parse($request['end_date'], $timezone)->endOfDay()
            ->setTimezone('UTC')->format('Y-m-d H:i:s');
        $request = $this->orderRepo->prepareOrderRequest($request);
        $orders = Order::with('orderType')
            ->whereIn('type_id', [9])
            ->join('users', 'users.id', '=', 'customer_id')
            ->join('users as store_owner', 'store_owner.id', '=', 'store_owner_user_id')
            ->select('orders.*', 'users.first_name as customer_first_name', 'users.last_name as customer_last_name', 'users.sponsor_id as customer_sponsor_id', 'store_owner.first_name as customer_sponsor_first_name','store_owner.last_name as customer_sponsor_last_name','store_owner.first_name as owner_first_name', 'store_owner.last_name as owner_last_name')
            ->whereBetween('orders.created_at', [$request['start_date'], $request['end_date']])
            ->search($request['search_term'], ['storeOwner.first_name', 'storeOwner.last_name', 'receipt_id'])
            ->orderBy($request['column'], $request['order']);
        if (isset($userId)) {
            $orders->where('store_owner_user_id', $userId);
        }
        if ($paginate) {
            return $orders->paginate($request['per_page']);
        } else {
            $orders = $orders->get();
            foreach ($orders as $order) {
                $order->updated_at = Carbon::parse($order->updated_at, 'UTC')->setTimezone($timezone)->toDateTimeString();
                $order->created_at = Carbon::parse($order->created_at, 'UTC')->setTimezone($timezone)->toDateTimeString();
            }
            return $orders;
        }
    }
}
