<?php

namespace App\Repositories\Eloquent;

use App\Models\OrderStatus;
use App\Models\Order;
use App\Repositories\Contracts\OrderStatusRepositoryContract;

class OrderStatusRepository implements OrderStatusRepositoryContract
{
    public function index()
    {
        if (cache()->has('order-status')) {
            $orderStatus = cache()->get('order-status');
        } else {
            $orderStatus = OrderStatus::select('id', 'name', 'position', 'visible', 'default')
                ->orderBy('position')
                ->get();
            cache()->forever('order-status', $orderStatus);
        }
        return $orderStatus;
    }

    public function find($id)
    {
        return OrderStatus::find($id);
    }

    public function create($request)
    {
        $status = OrderStatus::create($request);
        cache()->forget('order-status');
        return $status;
    }

    public function update($request, $id)
    {
        $status = OrderStatus::where('id', $id)->update($request);
        cache()->forget('order-status');
        return $status;
    }

    public function delete($id)
    {
        $status = OrderStatus::destroy($id);
        cache()->forget('order-status');
        return $status;
    }

    public function orderStatusCheck($status)
    {
        return Order::where('status', $status)->first();
    }
}
