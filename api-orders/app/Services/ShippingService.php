<?php

namespace App\Services;

class ShippingService implements ShippingServiceInterface
{

    public function findRate($sellerPid, $cartType, $subtotal)
    {
        if ($subtotal <= 0 && $cartType === 'custom-personal') {
            return (object)['id' => null, 'amount' => 0];
        }
        if ($cartType === 'wholesale' || $cartType === 'custom-wholesale') {
            $type = 'wholesale';
        } else {
            $type = 'retail';
        }

        $shippingCost = app('db')->table('shipping_rates')->select('id', 'amount')->where('user_pid', $sellerPid)
        ->where('type', $type)
        ->where('min', '<=', $subtotal)
        ->where('max', '>=', $subtotal)
        ->first();
        if ($shippingCost === null) {
            $shippingCost = app('db')->table('shipping_rates')->select('id', 'amount')->where('user_pid', $sellerPid)
            ->where('type', $type)
            ->where('max', null)->first();
        }
        return $shippingCost;
    }
}
