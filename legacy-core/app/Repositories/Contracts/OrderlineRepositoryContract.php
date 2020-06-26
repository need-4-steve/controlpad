<?php

namespace App\Repositories\Contracts;

use App\Models\Order;
use App\Models\Orderline;

interface OrderlineRepositoryContract
{
    public function create(Order $order, $cartlines);
    public function update(Orderline $orderline, array $inputs = []);
}
