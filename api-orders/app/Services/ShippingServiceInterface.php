<?php

namespace App\Services;

use App\Cart;

interface ShippingServiceInterface
{
    public function findRate($sellerPid, $cartType, $subtotal);
}
