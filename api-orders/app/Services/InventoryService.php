<?php

namespace App\Services;

use App\Checkout;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\RequestException;

class InventoryService implements InventoryServiceInterface
{
    private $inventoryUrl;

    public function __construct(Request $request)
    {
        $this->inventoryUrl = env('INVENTORY_URL', 'https://inventory.controlpadapi.com/api/v0');
    }

    public function getInventories($itemIds, $userPid)
    {
        $inventoryClient = new Client;
        try {
            $response = $inventoryClient->get(
                $this->inventoryUrl . '/items',
                [
                    'query' => [
                        'user_pid' => $userPid,
                        'item_ids' => $itemIds,
                        'expands' => [
                            'variant', 'product', 'variant_images', 'product_images'
                        ]
                    ],
                    'headers' => [
                        'Authorization' => app('utils')->getJWTAuthHeader()
                    ]
                ]
            );
            return json_decode($response->getBody());
        } catch (RequestException $re) {
            app('log')->error($re);
            abort(500);
        }
    }

    public function getBundles($bundleIds, $userPid)
    {
        $inventoryClient = new Client;
        try {
            $response = $inventoryClient->get(
                $this->inventoryUrl . '/bundles',
                [
                    'query' => [
                        'user_pid' => $userPid,
                        'bundle_ids' => $bundleIds,
                        'expands' => [
                            'items', 'variants', 'products', 'variant_images', 'product_images'
                        ]
                    ],
                    'headers' => [
                        'Authorization' => app('utils')->getJWTAuthHeader()
                    ]
                ]
            );
            return json_decode($response->getBody());
        } catch (RequestException $re) {
            app('log')->error($re);
            abort(500);
        }
    }

    public function reserveInventoryForCheckout(Checkout $checkout, $partialReserve, $useOrderlinePid = true)
    {
        if ($checkout->transfer_pid !== null) {
            $inventoryTransfer = $this->refreshReservationForCheckout($checkout, $partialReserve, $useOrderlinePid);
        } else {
            $inventoryTransfer = $this->createReservation($checkout->lines, $partialReserve, $useOrderlinePid);
            $checkout->transfer_pid = $inventoryTransfer->reservation_group_id;
            $checkout->save();
        }
        return $inventoryTransfer;
    }

    public function createReservation($lines, $partialReserve, $useOrderlinePid = true)
    {
        $reservations = $this->buildReservationLines($lines, $partialReserve, $useOrderlinePid);
        try {
            $inventoryClient = new Client;
            $response = $inventoryClient->post(
                $this->inventoryUrl . '/reservations',
                [
                    'json' => $reservations,
                    'headers' => [
                        'Authorization' => app('utils')->getJWTAuthHeader()
                    ]
                ]
            );
            return json_decode($response->getBody());
        } catch (RequestException $re) {
            app('log')->error($re);
            abort(500);
        }
    }

    private function refreshReservationForCheckout(Checkout $checkout, $partialReserve, $useOrderlinePid = true)
    {
        try {
            $inventoryClient = new Client;
            $response = $inventoryClient->get(
                $this->inventoryUrl . '/reservations/'.$checkout->transfer_pid.'/refresh',
                [
                    'headers' => [
                        'Authorization' => app('utils')->getJWTAuthHeader()
                    ]
                ]
            );
            return json_decode($response->getBody());
        } catch (RequestException $re) {
            if ($re->hasResponse()) {
                if ($re->getResponse()->getStatusCode() === 422) {
                    // Refresh failed because of expired reservation, just create a new one
                    $newReservation = $this->createReservation($checkout->lines, $partialReserve, $useOrderlinePid);
                    $checkout->transfer_pid = $newReservation->reservation_group_id;
                    $checkout->save();
                    return $newReservation;
                }
            }
            $this->logException($re);
            abort(500);
        }
    }

    public function cancelReservation($transferPid)
    {
        try {
            $inventoryClient = new Client;
            $response = $inventoryClient->delete(
                $this->inventoryUrl . '/reservations/'.$transferPid,
                [
                    'headers' => [
                        'Authorization' => app('utils')->getJWTAuthHeader()
                    ]
                ]
            );
            return true;
        } catch (RequestException $re) {
            app('log')->error($re);
            return false;
        }
    }

    public function transferReservation($transferPid, $userId, $userPid)
    {
        try {
            $inventoryClient = new Client;
            $response = $inventoryClient->post(
                $this->inventoryUrl . '/reservations/transfer',
                [
                    'json' => [
                        'reservation_group_id' => $transferPid,
                        'user_id' => $userId,
                        'user_pid' => $userPid
                    ],
                    'headers' => [
                        'Authorization' => app('utils')->getJWTAuthHeader()
                    ]
                ]
            );
            return json_decode($response->getBody());
        } catch (RequestException $re) {
            app('log')->error($re, ['transferPid' => $transferPid]);
            abort(500);
        }
    }

    public function confirmInventoryForOrder($order)
    {
        $updateMap = [];
        foreach ($order->lines as $key => $line) {
            // Skip bundle lines because it should be serialized into the bundle
            if (!isset($line->item_id) || !isset($line->bundle_id)) {
                // Serialized items contain the item ids
                foreach ($line->items as $key => $item) {
                    if (array_key_exists($item->id, $updateMap)) {
                        $updateMap[$item->id] += (isset($line->bundle_id) ? $line->quantity * $item->quantity : $line->quantity);
                    } else {
                        $updateMap[$item->id] = (isset($line->bundle_id) ? $line->quantity * $item->quantity : $line->quantity);
                    }
                }
            }
        }
        return $this->updateInventoryQuantities($order->customer_id, $order->buyer_pid, $updateMap);
    }

    public function assureInventoryCreated($order)
    {
        $updateMap = [];
        foreach ($order->lines as $key => $line) {
            // Skip bundle lines because it should be serialized into the bundle
            if (!isset($line->item_id) || !isset($line->bundle_id)) {
                // Serialized items contain the item ids
                foreach ($line->items as $key => $item) {
                    if (!array_key_exists($item->id, $updateMap)) {
                        $updateMap[$item->id] = 0;
                    }
                }
            }
        }
        return $this->updateInventoryQuantities($order->customer_id, $order->buyer_pid, $updateMap);
    }

    private function updateInventoryQuantities($userID, $userPID, $itemMap)
    {
        try {
            $requestBody = [
                'user' => [
                    'id' => $userID,
                    'pid' => $userPID
                ],
                'items' => [

                ]
            ];
            foreach ($itemMap as $key => $value) {
                $requestBody['items'][] = [
                    'id' => $key,
                    'quantity' => $value
                ];
            }

            $inventoryClient = new Client;
            $response = $inventoryClient->patch(
                $this->inventoryUrl . '/inventory-quantities',
                [
                    'json' => $requestBody,
                    'headers' => [
                        'Authorization' => app('utils')->getJWTAuthHeader()
                    ]
                ]
            );

            return json_decode($response->getBody());
        } catch (RequestException $re) {
            app('log')->error($re);
            return null;
        }
    }

    private function buildReservationLines($lines, $partialReserve, $useOrderlinePid = true)
    {
        $inventories = [];
        $transactions = [];
        foreach ($lines as $key => $line) {
            if (isset($line->item_id)) {
                $inventories[] = [
                    'id' => $line->items[0]->inventory_id,
                    'quantity' => $line->quantity,
                    'transaction_id' => $useOrderlinePid ?
                        (
                            isset($line->orderline_pid) ?
                            $line->orderline_pid :
                            null
                        ):
                        $line->pid
                ];
            } elseif (isset($line->bundle_id)) {
                // Bundles can never be a partial
                $items = [];
                foreach ($line->items as $key => $item) {
                    $items[] = [
                        'id' => $item->inventory_id,
                        'quantity' => $item->quantity * $line->quantity
                    ];
                }
                if (!empty($items)) {
                    $transactions[] = ['inventories' => $items, 'transaction_id' => $useOrderlinePid ? $line->orderline_pid : $line->pid];
                }
            }
        }
        $reservations = [];
        if (!empty($inventories)) {
            if ($partialReserve) {
                $reservations['inventories'] = $inventories;
            } else {
                // Wrap the inventories into a transaction if not allowing partial
                $transactions[] = ['inventories' => $inventories, 'transaction_id' => 'items'];
            }
        }
        if (!empty($transactions)) {
            $reservations['transactions'] = $transactions;
        }
        return $reservations;
    }
}
