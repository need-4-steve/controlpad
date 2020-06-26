<?php

namespace App\Services\Shippo;

use App\Repositories\Eloquent\ShipmentRepository;
use App\Repositories\Eloquent\BatchLabelRepository;
use Shippo;

class ShippoCommon
{
    public function __construct()
    {
        Shippo::setApiKey(env('SHIPPO_API_KEY'));
    }

    /**
     * Format for a response when an error occurs.
     *
     * @param Shippo_Error $error
     * @return array $error
     */
    public function errorResponse($error)
    {
        if (is_null($error->httpStatus)) {
            return [
                'error' => 'Missing SHIPPO_API_KEY',
                'httpStatus' => 400
            ];
        }

        return [
            'error' => json_decode($error->httpBody),
            'httpStatus' => $error->httpStatus
        ];
    }

    /**
     * Formats address to be sent to Shippo.
     *
     * @param Address $address
     * @param User $user
     * @return array
     */
    public function formatAddress($address, $user)
    {
        return [
            'name' => $address->name,
            'street1' => $address->address_1,
            'street2' => $address->address_2,
            'city' => $address->city,
            'state' => $address->state,
            'zip' => $address->zip,
            'email' => $user->email,
            'phone' => $user->phone_number,
            'country' => 'US',
            'object_purpose' => 'PURCHASE',
        ];
    }

    public function formatOrder($order, $to_address, $address_from)
    {
        $items = [];
        foreach ($order->lines as $line) {
            if (isset($line->item)) {
                $items[] = [
                    'currency'      => 'USD',
                    'quantity'      => $line->quantity,
                    'sku'           => $line->item->manufacturer_sku,
                    'title'         => $line->item->product->name,
                    'variant_title' => $line->item->size,
                    'total_amount'  => $line->price,
                    'weight'        => $line->item->weight,
                    'weight_unit'   => 'lb'
                ];
            } elseif (isset($line->bundle)) {
                $items[] = [
                    'currency'      => 'USD',
                    'quantity'      => $line->quantity,
                    'sku'           => '',
                    'title'         => $line->bundle->name,
                    'variant_title' => 'Pack',
                    'total_amount'  => $line->price,
                    'weight'        => 0,
                    'weight_unit'   => 'lb'
                ];
            }
        }

        $body = [
            'to_address'             => $to_address,
            'address_from'           => $address_from,
            'items'                  => $items,
            'order_number'           => $order->receipt_id,
            'order_status'           => 'PAID',
            'shipping_cost'          => $order->total_shipping,
            'shipping_cost_currency' => 'USD',
            'shipping_method'        => (isset($order->shipping->name)) ? $order->shipping->name : ' ',
            'subtotal_price'         => $order->subtotal_price,
            'total_price'            => $order->total_price,
            'total_tax'              => $order->total_tax,
            'currency'               => 'USD'
        ];
        return $body;
    }

    /**
     * Recursion to get the message into a string response back from a shippo
     * object because the messages can be nested many levels deep.
     *
     * @param array $messages
     * @return string $message
     */
    public function getMessages($messages)
    {
        if (is_array($messages)) {
            foreach ($messages as $key => $message) {
                if (is_object($message)) {
                    $message = $this->getMessages($message->__toArray());
                } elseif (is_array($message)) {
                    $message = $this->getMessages($message);
                } else {
                    return $message;
                }
                if (is_string($message)) {
                    if ($key === 0) {
                        return $message;
                    }
                    return $key."->".$message;
                }
            }
        }
    }

    /**
     * Marks up shipping cost.
     *
     * @param array $rate
     * @return array $rate
     */
    public function markupRate($rate)
    {
        $rate['markup'] = round($rate['amount'] * 0.10, 2);
        $rate['total_price'] = $rate['amount'] + $rate['markup'];
        return $rate;
    }

    public function toArray($data)
    {
        if (is_object($data)) {
            return $data->__toArray();
        }
        return null;
    }
}
