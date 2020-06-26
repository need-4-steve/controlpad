<?php

namespace App\Services\Interfaces\V0;

interface OrderServiceInterface
{
    public function getCart($cartPid);
    public function createCheckout($subscription, $buyer);
    public function checkout($subscription, $buyer, $checkout);
}
