<?php

namespace App\Services;

use App\Checkout;

interface InventoryServiceInterface
{
    public function getInventories($itemIds, $userId);
    public function getBundles($bundleIds, $userPid);
    public function reserveInventoryForCheckout(Checkout $checkout, $partialReserve);
    public function cancelReservation($transferPid);
    public function transferReservation($transferPid, $userId, $userPid);
}
