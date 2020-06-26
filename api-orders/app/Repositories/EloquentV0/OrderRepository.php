<?php

namespace App\Repositories\EloquentV0;

use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Order;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository implements OrderRepositoryInterface
{
    private function standardSelectAndJoin(array $additionalSelects = [])
    {
        $standardSelects = [
            'orders.id as id',
            'orders.pid',
            'orders.receipt_id',
            'orders.confirmation_code',
            'orders.type_id',
            'order_types.name as type_description',
            'customer_id as buyer_id',
            'orders.buyer_pid',
            'orders.buyer_first_name',
            'orders.buyer_last_name',
            'orders.buyer_email',
            'store_owner_user_id as seller_id',
            'seller_pid',
            'seller_name',
            'total_price',
            'subtotal_price',
            'total_discount',
            'total_shipping',
            'total_tax',
            'transaction_id',
            'gateway_reference_id',
            'cash',
            'orders.payment_type',
            'orders.cash_type',
            'source',
            'paid_at',
            'orders.coupon_id',
            'orders.status',
            'orders.created_at as created_at',
            'orders.updated_at as updated_at',
            'orders.billing_address',
            'orders.shipping_address',
            'orders.inventory_received_at',
        ];
        $selects = array_merge($standardSelects, $additionalSelects);
        $orders = Order::select($selects);
        $orders->join('users', 'users.id', '=', 'customer_id');
        $orders->leftJoin('order_types', 'order_types.id', '=', 'type_id');
        return $orders;
    }

    private function handleStandardParamsAndPaginate(Builder $query, array $params) : LengthAwarePaginator
    {
        $inOrder = 'asc';
        if (isset($params['in_order'])) {
            $inOrder = $params['in_order'];
        }
        if (isset($params['status'])) {
            $query->where('orders.status', $params['status']);
        }
        if (isset($params['type_id'])) {
            $query->whereIn('orders.type_id', $params['type_id']);
        }
        if (isset($params['seller_id'])) {
            $query->where('orders.store_owner_user_id', $params['seller_id']);
        }
        if (isset($params['buyer_id'])) {
            $query->where('orders.customer_id', $params['buyer_id']);
        }
        if (isset($params['seller_pid'])) {
            $query->where('orders.seller_pid', $params['seller_pid']);
        }
        if (isset($params['buyer_pid'])) {
            $query->where('orders.buyer_pid', $params['buyer_pid']);
        }
        if (isset($params['coupon_id'])) {
            $query->where('orders.coupon_id', $params['coupon_id']);
        }
        if (isset($params['start_date']) && isset($params['end_date']) && isset($params['date_type'])) {
            $query->whereBetween('orders.' . $params['date_type'], [$params['start_date'], $params['end_date']]);
        } elseif (isset($params['start_date']) && isset($params['end_date'])) {
            $query->whereBetween('orders.created_at', [$params['start_date'], $params['end_date']]);
        }
        if (isset($params['orderlines']) && $params['orderlines']) {
            $query->with('lines');
        }
        if (isset($params['tracking']) && $params['tracking']) {
            $query->with('tracking');
        }
        if (isset($params['event_id'])) {
            $query->whereHas('lines', function ($query) use ($params) {
                $query->where('event_id', $params['event_id']);
            });
        }
        if (isset($params['search_term']) && $params['search_term'] !== '') {
            $term = filter_var($params['search_term'], FILTER_SANITIZE_STRING);
            $query->whereRaw('MATCH(receipt_id, buyer_first_name, buyer_last_name) AGAINST ("'.$term.'")');
        }
        if (isset($params['sort_by'])) {
            $query->orderBy($params['sort_by'], $inOrder);
        }
        // paginate
        if (isset($params['per_page'])) {
            $results = $query->paginate($params['per_page']);
        } else {
            $results = $query->paginate(200);
        }

        return $results;
    }

    public function index($request) : LengthAwarePaginator
    {
        $orders = $this->standardSelectAndJoin();
        return $this->handleStandardParamsAndPaginate($orders, $request);
    }

    public function orderById($id, array $params = [])
    {
        $order = $this->standardSelectAndJoin();
        $relationships = ['coupon'];
        if (isset($params['orderlines']) && $params['orderlines']) {
            array_push($relationships, 'lines');
        }
        if (isset($params['tracking']) && $params['tracking']) {
            array_push($relationships, 'tracking');
        }
        return $order->with($relationships)->where('orders.id', $id)->first();
    }

    public function orderByReceiptId($receiptId, $params)
    {
        $order = $this->standardSelectAndJoin();
        $relationships = ['coupon'];
        if (isset($params['orderlines']) && $params['orderlines']) {
            array_push($relationships, 'lines');
        }
        if (isset($params['tracking']) && $params['tracking']) {
            array_push($relationships, 'tracking');
        }
        return $order->with($relationships)->where('orders.receipt_id', $receiptId)->first();
    }

    public function edit($id, $params) : bool
    {
        return Order::where('id', $id)->update($params);
    }

    public function editMultiple($ids, $param) : bool
    {
        return Order::whereIn('id', $ids)->update($param);
    }
}
