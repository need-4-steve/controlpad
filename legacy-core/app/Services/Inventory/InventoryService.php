<?php

namespace App\Services\Inventory;

use App\Repositories\Eloquent\InventoryRepository;
use DB;
use Log;
use App\Models\Inventory;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class InventoryService
{
    protected $inventoryRepo;

    public function __construct(InventoryRepository $inventoryRepository)
    {
        $this->inventoryRepo = $inventoryRepository;
        $this->inventoryUrl = ENV('INVENTORY_API_URL', 'https://inventory.controlpadapi.com/api/v0');
    }

    public function subtractInventory($lines, $bundles = null)
    {
        return $this->inventoryRepo->updateInventory($lines, null, $subtract = true);
    }

    public function addToRepInventory($lines, $rep, $newUserRegistration = false)
    {
        $inv = $this->inventoryRepo->updateInventory($lines, $rep->id, $subtract = false, $newUserRegistration);
        return $inv;
    }

    public function deductRepInventory($lines, $rep)
    {
        $inv = $this->inventoryRepo->updateInventory($lines, $rep->id, $subtract = true);
        return $inv;
    }

    public function reserveInventory($cart)
    {
        $reservations = $this->buildReservationLines($cart->allLines, false);
        try {
            $inventoryClient = new Client;
            $response = $inventoryClient->post(
                $this->inventoryUrl . '/reservations',
                [
                    'json' => $reservations,
                    'headers' => [
                        'Authorization' => 'Bearer ' . \App\Services\Authentication\JWTAuthService::getApiJWT()
                    ]
                ]
            );
            return json_decode($response->getBody());
        } catch (RequestException $re) {
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
                        'Authorization' => 'Bearer ' . \App\Services\Authentication\JWTAuthService::getApiJWT()
                    ]
                ]
            );
            return true;
        } catch (RequestException $re) {
            $this->logException($re);
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
                        'Authorization' => 'Bearer ' . \App\Services\Authentication\JWTAuthService::getApiJWT()
                    ]
                ]
            );
            return json_decode($response->getBody());
        } catch (RequestException $re) {
            $this->logException($re);
            return null;
        }
    }

    private function buildReservationLines($lines, $partialReserve)
    {
        $inventories = [];
        $transactions = [];
        foreach ($lines as $key => $line) {
            if (isset($line->item_id)) {
                $inventories[] = [
                    'id' => json_decode($line->items)[0]->inventory_id,
                    'quantity' => $line->quantity,
                    'transaction_id' => strval($line->id)
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
                    $transactions[] = ['inventories' => $items, 'transaction_id' => strval($line->id)];
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

    private function logException($re)
    {
        if ($re->hasResponse()) {
            $responseBody = Psr7\str($re->getResponse());
        } else {
            $responseBody = null;
        }
        app('log')->error(
            $re,
            [
                'request' => Psr7\str($re->getRequest()),
                'response' => $responseBody
            ]
        );
    }

    public function checkAvailablities()
    {
        $cart = session()->get('cart');
        if ($cart && !empty($cart->lines)) {
            $messages = [];
            $error = false;
            DB::beginTransaction();
            // Check single items in cart.
            foreach ($cart->lines as $line) {
                if (session()->has('store_owner') && session()->get('store_owner.seller_type_id') != 1) {
                    $userId = session('store_owner.id');
                } else {
                    $userId = config('site.apex_user_id');
                }
                $quantityAvailable = $this->inventoryRepo->quantityAvailable($line['item_id'], $userId);
                if ($quantityAvailable === null) {
                    $error = true;
                    $messages[] = 'There was an error, and the selected item could not be found.';
                }
                if ($quantityAvailable < $line['quantity']) {
                    $error = true;
                    $messages[] = 'The selected quantity for '
                                    . $line->item->product->name
                                    . ' - '
                                    . $line->item->size
                                    . ' is NOT available. Quantity Available: '
                                    . $quantityAvailable;
                }
            }
            foreach ($cart->bundles as $bundle) {
                $bundleCheck = $this->checkBundleInventoryAvailability($bundle, $bundle->pivot->quantity);
                if ($bundleCheck['error'] === true) {
                    $error = true;
                    $messages[] = 'The selected quantity for Pack-'
                                        . $bundle->name
                                        . ' is NOT available. Quantity Available:'
                                        . $bundleCheck['quantityAvailable'];
                }
            }
            DB::commit();
            return [
                'error' => $error,
                'message' => $messages
            ];
        }
        return false;
    }

    public function checkBundleInventoryAvailability($bundle, $quantity)
    {
        $userId = config('site.apex_user_id');
        $lowestQuantity = null;
        $quantityError = false;
        foreach ($bundle->items as $item) {
            $quantityAvailable = $this->inventoryRepo->quantityAvailable($item->id, $userId, true);
            if ($quantityAvailable === null) {
                $error = true;
                $messages[] = 'There was an error, and the selected item could not be found.';
            }
            if ($lowestQuantity === null || $lowestQuantity > $quantityAvailable/$item->pivot->quantity) {
                $lowestQuantity = floor($quantityAvailable/$item->pivot->quantity);
            }
            if ($quantityAvailable === null) {
                $error = true;
                $messages[] = 'There was an error, and the selected item could not be found.';
            }
            if ($quantityAvailable < ($quantity * $item->pivot->quantity)) {
                $quantityError = true;
            }
        }
        if ($quantityError === true) {
            return ['error' => true, 'quantityAvailable' => $lowestQuantity];
        }
        return ['error' => false, 'quantityAvailable' => $lowestQuantity];
    }

    /**
     * Reserves inventory for an invoice
     *
     * @param Array $items
     * @param Integer $user_id
     * @return void
     */
    public function stageInvoice($items, $user_id)
    {
        DB::beginTransaction();
        foreach ($items as $item) {
            $stagedInventory = $this->inventoryRepo->getInventoryByUserAndItem($item['item_id'], $user_id);
            $stagedInventory->quantity_available -= $item['quantity'];
            $stagedInventory->quantity_staged += $item['quantity'];
            $stagedInventory->save();
        }
        DB::commit();
    }

    /**
     * Unstages inventory from an invoice to not be reserved anymore
     *
     * @param Array $items
     * @param Integer $user_id
     * @return void
     */
    public function unstageInvoice($items, $user_id)
    {
        DB::beginTransaction();
        foreach ($items as $item) {
            $stagedInventory = $this->inventoryRepo->getInventoryByUserAndItem($item->id, $user_id);
            $stagedInventory->quantity_available += $item->pivot->quantity;
            $stagedInventory->quantity_staged -= $item->pivot->quantity;
            $stagedInventory->save();
        }
        DB::commit();
    }

    public function checkStagedAvailablities($invoice)
    {
        $error = false;
        $messages = [];

        $items = $invoice->invoiceItems;
        $itemsIds = array_pluck($items->toArray(), 'id');
        if ($invoice->type_id === 9) {
            $sellerId = config('site.apex_user_id');
        } else {
            $sellerId = $invoice->store_owner_user_id;
        }

        foreach ($items as $item) {
            $inv = Inventory::where('user_id', $sellerId)->where('item_id', $item->id)->first();
            $invDiff = $inv->quantity_staged - $item->pivot->quantity;
            if ($invDiff < 0) {
                $error = true;
                $messages[] = 'There is not enough staged inventory ('. $invDiff .') for product: '.$item->size.' '.$item->product->name.'.';
            }
        }

        return [
            'error' => $error,
            'message' => $messages
        ];
    }

    public function transferFulfilledByCorporateProduct($order)
    {
        DB::beginTransaction();
        foreach ($order->lines as $line) {
            if ($line->item->product->type_id === 5) {
                $inventory = $line->item->inventory->where('user_id', config('site.apex_user_id'))->first();
                if ($inventory->owner_id === $order->customer_id) {
                    $repInventory = $line->item->inventory->where('user_id', $order->customer_id)->first();
                    if ($repInventory) {
                        $repInventory->quantity_available += $line->quantity;
                        $repInventory->save();
                    } else {
                        $repInventory = $this->inventoryRepo->create($order->customer_id, $line->item, null, $line->quantity);
                    }
                    $this->inventoryRepo->checkToDisable($repInventory, $order->customer_id);
                    $inventory->owner_id = config('site.apex_user_id');
                    $inventory->save();
                    $order->type_id = 8; // Transfer Order
                    $order->save();
                }
            }
        }
        DB::commit();
        return $order;
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
                        'Authorization' => 'Bearer ' . \App\Services\Authentication\JWTAuthService::getApiJWT()
                    ]
                ]
            );

            return json_decode($response->getBody());
        } catch (RequestException $re) {
            app('log')->error($re);
            return null;
        }
    }
}
