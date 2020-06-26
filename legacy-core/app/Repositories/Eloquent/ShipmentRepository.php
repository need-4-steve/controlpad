<?php

namespace App\Repositories\Eloquent;

use App\Models\Carrier;
use App\Models\ParcelTemplate;
use App\Models\Shipment;
use App\Repositories\Eloquent\AuthRepository;

class ShipmentRepository
{
    /**
     * ShipmentRepository constructor.
     *
     * @param AuthRepository $authRepo
     */
    public function __construct(AuthRepository $authRepo)
    {
        $this->authRepo = $authRepo;
    }

    /**
     * Create a single instance of a purchased label.
     *
     * @param array $shipment
     * @param string $transactionId,
     * @param double $rate
     * @param int $orderId
     * @return Shipment $shipment
     */
    public function create($shipment, $transactionId, $rate, $orderId = null)
    {
        $shipment['order_id'] = $orderId;
        $shipment['transaction_id'] = $transactionId;
        $shipment['amount'] = $rate['amount'];
        $shipment['markup'] = $rate['markup'];
        $shipment['total_price'] = $rate['total_price'];
        $shipment['user_id'] = $this->authRepo->getOwnerId();
        $shipment = Shipment::create($shipment);
        return $shipment;
    }

    /**
     * Get an index of available Carriers and their services for shipping.
     *
     * @return Carrier
     */
    public function getCarriers()
    {
        return Carrier::whereHas('serviceLevel')
                ->whereNotNull('account_id')
                ->with('serviceLevel')
                ->get();
    }
}
