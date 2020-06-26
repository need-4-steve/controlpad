<?php

namespace App\Services\Shippo;

use App\Jobs\SendShippingOrder;
use App\Services\Shippo\Classes\Shippo_Order;
use App\Models\Carrier;
use Log;
use Shippo;
use Shippo_Address;
use Shippo_CarrierAccount;
use Shippo_Rate;
use Shippo_Error;
use Shippo_Shipment;
use Shippo_Transaction;

class ShippingServiceExport extends ShippoCommon
{

    public function __construct()
    {
    }

    public function queOrder($order)
    {
        $_settings = app('globalSettings');
        if ($_settings->getGlobal('auto_transfer_orders', 'value')) {
            $job = new SendShippingOrder($order);
            dispatch($job);
        }
    }

    /**
     * Write orders to third party shipping service.
     *
     * @param array $receiptIds each receipt_id of an order
     * @return array $transferredOrders
     */
    public function exportOrder($order)
    {
        $_settings = app('globalSettings');
        if ($_settings->getGlobal('shipping_team_id', 'value') !== 'company') {
            return;
        }
        try {
            $transferredOrders = [];
            $order->load(
                'lines.item.product',
                'shippingAddress',
                'storeOwner.businessAddress',
                'customer',
                'shipping'
            );
            if ($order->type_id === 1 or // Corporate to Rep
                $order->type_id === 2 or // Corporate to Customer
                $order->type_id === 5 or // Corporate to Admin
                $order->type_id === 6 or // Fulfilled by Corporate
                $order->type_id === 7 or // Mixed
                $order->type_id === 8 or // Transfer Inventory
                $order->type_id === 9    // Affiliate
            ) {
                if ($order->type_id === 1) {
                    foreach ($order->lines as $key => $line) {
                        // unset orderlines that don't need to be shipped
                        if (isset($line->item) and $line->item->product->type_id === 5) {
                            // product type_id 5 = fulfilled by corporate
                            unset($order->lines[$key]);
                        } elseif (!isset($line->item_id) and $line->bundle->type_id === 2) {
                            // bundle type_id 2 = fulfilled by corporate
                            unset($order->lines[$key]);
                        }
                    }
                }
                if (count($order->lines) > 0) {
                    try {
                        $to_address = Shippo_Address::create($this->formatAddress($order->shippingAddress, $order->customer))->__toArray();
                        $address_from = Shippo_Address::create($this->formatAddress($order->storeOwner->businessAddress, $order->storeOwner))->__toArray();
                        $formatedOrder = $this->formatOrder($order, $to_address['object_id'], $address_from['object_id']);
                        $transferredOrders[] = Shippo_Order::create($formatedOrder);
                    } catch (Shippo_Error $error) {
                        logger()->error($this->errorResponse($error));
                    }
                }
            }
            return $transferredOrders;
        } catch (\Exception $e) {
            logger()->error(['class ShippingService function exportOrder', $e->getMessage()]);
        }
    }
}
