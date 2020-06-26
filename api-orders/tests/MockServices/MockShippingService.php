<?php

namespace Test\MockServices;

use App\Cart;

class MockShippingService implements \App\Services\ShippingServiceInterface
{
    private $wholesaleRate = [
        'id' => 1,
        'amount' => 10.00
    ];

    private $retailRate = [
        'id' => 2,
        'amount' => 5.00
    ];

    public function findRate($sellerPid, $cartType, $subtotal)
    {
        if ($subtotal <= 0 && $cartType === 'custom-personal') {
            return (object)['id' => null, 'amount' => 0];
        }
        if ($cartType === 'wholesale' || $cartType === 'custom-wholesale') {
            return (object) $this->wholesaleRate;
        } else {
            return (object) $this->retailRate;
        }
    }
}
