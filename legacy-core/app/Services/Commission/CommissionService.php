<?php

namespace App\Services\Commission;

use App\Models\Inventory;
use App\Services\Settings\SettingsService;
use GuzzleHttp;
use App\Models\User;
use App\Models\Order;
use App\Models\Orderline;
use App\Jobs\SendCommEngineUser;
use App\Jobs\SendCommEngineOrder;
use Carbon\Carbon;
use App\Services\Commission\CommissionServiceInterface;

class CommissionService implements CommissionServiceInterface
{
    /**
     * Sends data about an Order to the Commission Engine.
     * withTrashed() needs to be used because of backfill and records in a model might have been deleted.
     *
     * @param Order $order
     * @param bool $initialize used for backfill
     * @return array $receipts returns headers foreach orderline that was sent
     */
    public function addReceipt($order, $initialize = false)
    {
        $line = null;
        try {
            $_settings = app('globalSettings');
            if ($_settings->getGlobal('use_commission_engine', 'value')
            and $this->checkCommissionableOrderType($order)
            or $initialize == true
            and $this->checkCommissionableOrderType($order)) {
                $client = new GuzzleHttp\Client();
                $receipts = [];
                $bundlePercentDiscount = [];
                foreach ($order->lines as $line) {
                    if (isset($line->item_id) and !$line->in_comm_engine) {
                        // Prevent imported invenotry from being sent over
                        if ($order->type_id == 9) { //affiliate order
                            $inventoryOwner = config('site.apex_user_id');
                        } else {
                            $inventoryOwner = $order->store_owner_user_id;
                        }
                        $inventory = Inventory::where('user_id', $inventoryOwner)
                            ->where('item_id', $line->item_id)
                            ->first();
                        $remainingImportedQuantity = $inventory->quantity_imported - $line->quantity;
                        // If there is remaining imported inventory just update the record
                        if ($remainingImportedQuantity > 0) {
                            $inventory->update(['quantity_imported' => $remainingImportedQuantity]);
                            Orderline::where('id', $line->id)->update(['in_comm_engine' => true]);
                            continue;
                        } elseif ($remainingImportedQuantity <= 0 && $inventory->quantity_imported > 0) {
                            // else if the remaining imported inventory is less then 0 and originally the inventory had been imported
                            // send the quanitity to the commission engine reflecting the amount of inventory that wasn't imported
                            $line->quantity = (-1 * $remainingImportedQuantity);

                            if ($line->quantity === 0) {
                                $inventory->update(['quantity_imported' => 0]);
                                Orderline::where('id', $line->id)->update(['in_comm_engine' => true]);
                                continue;
                            }
                        }
                        $headers = $this->getReceiptHeaders($order, $line, $bundlePercentDiscount);
                        if ($headers['invtype'] !== 0 and $headers['commissionable'] !== 'non-personal-volume') {
                            $receipts[] = $headers;
                            // Needs a delay or else commission engine might not have enough resources to allocate
                            usleep(1000);
                            $response = $client->post(env('COMM_URL'), [
                                'headers' => $headers,
                            ]);
                        }
                        Orderline::where('id', $line->id)->update(['in_comm_engine' => true]);
                        $inventory->update(['quantity_imported' => 0]);
                    } elseif (isset($line->bundle_id) and !isset($line->item_id)) {
                        $bundle = $line->bundle()->withTrashed()->first();
                        $bundlePercentDiscount[$bundle->id] = $this->getBundleDiscountPercent($bundle, $line->price);
                        Orderline::where('id', $line->id)->update(['in_comm_engine' => true]);
                    }
                }
                if ($order->total_discount > 0 and
                    $order->type_id !== 6 and
                    $order->type_id !== 7 and
                    $order->comm_engine_status_id === 1 and
                    count($receipts) > 0 or
                    $order->total_discount > 0 and
                    $order->type_id !== 6 and
                    $order->type_id !== 7 and
                    $order->comm_engine_status_id === 6 and
                    count($receipts) > 0
                ) {
                    $headers = $this->getNegativeReceiptHeaders($order);
                    $receipts[] = $headers;
                    if ($headers['invtype'] !== 0 and $headers['commissionable'] !== 'non-personal-volume') {
                        $response = $client->post(env('COMM_URL'), [
                            'headers' => $headers,
                        ]);
                    }
                }
                // committed to commission engine
                $order->update(['comm_engine_status_id' => 2]);
                return $receipts;
            } elseif ($_settings->getGlobal('use_commission_engine', 'value') or $initialize) {
                // not commissionable in commission engine
                $order->update(['comm_engine_status_id' => 3]);
            }
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'retcount == 0') !== false) {
                $order->update(['comm_engine_status_id' => 9]);
            } else {
                // error while sending over to commission engine
                $order->update(['comm_engine_status_id' => 4]);
                if ($initialize == false) {
                    logger()->warning('Commission Engine: error in sending an order', [
                        'message' => $e->getMessage(),
                        'receipt_id' => $order->receipt_id,
                        'fingerprint' => 'CommissionService addReceipt',
                    ]);
                }
            }
        }
    }

    /**
     * Cancel an Order that has been sent to the Commission Engine
     *
     * @param String $orderReceiptId
     * @return GuzzleHttp\Client $response returns a Guzzle Response
     */
    public function cancelOrder($order)
    {
        $_settings = app('globalSettings');
        try {
            if ($_settings->getGlobal('use_commission_engine', 'value') && $order->comm_engine_status_id === 2) {
                $headers = [
                    'authemail'         => env('COMM_EMAIL'),
                    'apikey'            => env('COMM_API_KEY'),
                    'systemid'          => env('COMM_SYSTEM_ID'),
                    'command'           => 'cancelreceipt',
                    'metadataonadd'     => $order->receipt_id,
                ];
                $client = new GuzzleHttp\Client();
                $response = $client->post(env('COMM_URL'), [
                    'headers' => $headers,
                ]);
                $order->update(['comm_engine_status_id' => 8]); // Order has been cancelled in the commission engine.
                return $response;
            }
        } catch (\Exception $e) {
            $order->update(['comm_engine_status_id' => 7]); // Order tried to be cancelled in the commission engine but failed.
            logger()->warning('Commission Engine: error in cancelling an order', [
                'message' => $e->getMessage(),
                'receipt_id' => $order->receipt_id,
                'fingerprint' => 'CommissionService cancelOrder',
            ]);
        }
    }

    public function commitNewOrders()
    {
        $commSetting = app('globalSettings')->getGlobal('use_commission_engine', 'value');
        if ($commSetting == true) {
            $maxId = Order::where('comm_engine_status_id', 1)->max('id');
            if (empty($maxId)) {
                return;
            }
            $lastId = 0;
            do {
                $orders = Order::where('comm_engine_status_id', 1)
                            ->where('id', '>', $lastId)->where('id', '<=', $maxId)
                            ->where('created_at', '>', Carbon::now()->subDays(31))
                            ->where('created_at', '>=', '2018-02-01 07:00:00') // Hard coded here to prevent mobile orders from being written over before this date.
                            ->limit(50)->get();
                foreach ($orders as $order) {
                    $lastId = $order->id;
                    $order->update(['comm_engine_status_id' => 6]);
                    $this->addReceipt($order);
                }
            } while ($orders !== null && $orders->isNotEmpty() && $lastId < $maxId);
        }
    }

    /**
     * Sends data about a User to the Commission Engine.
     *
     * @param User $user
     * @param bool $initialize used for backfill
     * @return array $commResponse
     */
    public function addUser($user, $initialize = false)
    {
        try {
            $_settings = app('globalSettings');
            if ($_settings->getGlobal('use_commission_engine', 'value') or $initialize == true) {
                $sponsor = User::where('id', $user->sponsor_id)->withTrashed()->first();
                if (isset($sponsor->comm_engine_status_id) and $sponsor->comm_engine_status_id !== 2) {
                    $this->addUser($sponsor, true);
                }
                $client = new GuzzleHttp\Client();
                $user->load('businessAddress');
                $headers = [
                    'authemail'         => env('COMM_EMAIL'),
                    'apikey'            => env('COMM_API_KEY'),
                    'systemid'          => env('COMM_SYSTEM_ID'),
                    'command'           => 'adduser',
                    'userid'            => $user->id,
                    'sponsorid'         => $user->sponsor_id,
                    'parentid'          => $user->sponsor_id,
                    'signupdate'        => $this->getSignupDate($user),
                    'usertype'          => $this->getUserType($user),
                    'firstname'         => preg_replace('/[^ \w-]/', "", $user->first_name),
                    'lastname'          => preg_replace('/[^ \w-]/', "", $user->last_name),
                    'email'             => $user->email,
                    'cell'              => ((isset($user->phone_number) and $user->phone_number !== '') ? str_replace('-', '', $user->phone_number) : null),
                    'address'           => ((isset($user->businessAddress->address_1)) ? preg_replace('/[^ \w-]/', "", $user->businessAddress->address_1 . ' '. $user->businessAddress->address_2) : null),
                    'city'              => ((isset($user->businessAddress->city)) ? preg_replace('/[^ \w-]/', "", $user->businessAddress->city) : null),
                    'state'             => ((isset($user->businessAddress->state)) ? $user->businessAddress->state : null),
                    'zip'               => ((isset($user->businessAddress->zip)) ? $user->businessAddress->zip : null),
                ];
                if ($user->comm_engine_status_id === 2 or $this->findUser($user)) {
                    $headers['command'] = 'edituser';
                }
                $commResponse = $client->post(env('COMM_URL'), array_filter([
                    'headers' => $headers
                ]));
                $user->update(['comm_engine_status_id' => 2]);
                return json_decode($commResponse->getBody(), 1);
            }
        } catch (\Exception $e) {
            $user->update(['comm_engine_status_id' => 4]);
            if ($initialize == false) {
                logger()->warning('Commission Engine: error in sending a user', [
                    'message' => $e->getMessage(),
                    'user' => ['id' => $user->id, 'name' => $user->first_name.' '.$user->last_name, 'email' => $user->email],
                    'fingerprint' => 'CommissionService addUser',
                ]);
            }
        }
    }

    /**
     * Checks to see if the user is already in the commission engine.
     *
     * @param User $user
     * @return bool
     */
    public function findUser($user)
    {
        try {
            $client = new GuzzleHttp\Client();
            $headers = [
                'authemail'         => env('COMM_EMAIL'),
                'apikey'            => env('COMM_API_KEY'),
                'systemid'          => env('COMM_SYSTEM_ID'),
                'command'           => 'getuser',
                'userid'            => $user->id,
            ];
            $findResponse = $client->post(env('COMM_URL'), [
                'headers' => $headers
            ]);
            $response = json_decode($findResponse->getBody(), 1);
            if (isset($response)) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Puts an Order in a queue to be sent to the commission engine.
     *
     * @param Order $order
     */
    public function queOrder($order)
    {
        $_settings = app('globalSettings');
        if ($_settings->getGlobal('use_commission_engine', 'value')) {
            $job = (new SendCommEngineOrder($order, false))->delay(Carbon::now()->addSeconds(3));
            dispatch($job);
        }
    }

    /**
     * Puts a User in a queue to be sent to the commission engine.
     *
     * @param Order $order
     */
    public function queUser($user)
    {
        $_settings = app('globalSettings');
        if ($_settings->getGlobal('use_commission_engine', 'value')) {
            $job = (new SendCommEngineUser($user, false))->delay(Carbon::now()->addSeconds(3));
            dispatch($job);
        }
    }

    /**
     * Checks to see if the order type is commissionable.
     *
     * @param Order $order
     * @return bool
     */
    private function checkCommissionableOrderType($order)
    {
        if ($order->type_id === 1    // Corporate to Rep
            or $order->type_id === 3 // Rep to Customer
            or $order->type_id === 6 // Fulfilled by Corporate
            and $order->store_owner_user_id === config('site.apex_user_id')
            or $order->type_id === 7 // Mixed
            and $order->store_owner_user_id === config('site.apex_user_id')
            or $order->type_id === 9
        ) {
            return true;
        }
        return false;
    }

    /**
     * If the orderline is part of a bundle it calculates the fraction of the price of what the bundle costs.
     * Otherwise it returns the price of the item.
     * withTrashed() needs to be used because of backfill and records in a model might have been deleted.
     *
     * @param Order $order
     * @param Orderline $orderline
     * @param Array $bundlePercentDiscount
     * @return double price
     */
    private function getAmount($order, $orderline, $bundlePercentDiscount)
    {
        if (isset($orderline->bundle_id)) {
            if ($bundlePercentDiscount[$orderline->bundle()->withTrashed()->first()->id] == 0) {
                return 0;
            }
            return $orderline->item()->withTrashed()->first()->wholesalePrice->price / $bundlePercentDiscount[$orderline->bundle()->withTrashed()->first()->id];
        }
        return $orderline->price;
    }

    /**
     * Calculates the fraction of what each item will cost compared to what the total price of the bundle is.
     * withTrashed() needs to be used because of backfill and records in a model might have been deleted.
     *
     * @param Bundle $bundle
     * @param double $price
     * @return double
     */
    private function getBundleDiscountPercent($bundle, $price)
    {
        $totalPrice = 0;
        foreach ($bundle->items()->withTrashed()->get() as $item) {
            $totalPrice += $item->wholesalePrice->price * $item->pivot->quantity;
        }

        // prevents division by zero error
        if ($price == 0) {
            return 0;
        }
        return $totalPrice / $price;
    }

    /**
     * Returns if an Orderline is commissionable.
     * Commission engine validation restricts it to being a string instead of a boolean.
     * withTrashed() needs to be used because of backfill and records in a model might have been deleted.
     *
     * @param Orderline $line
     * @return string
     */
    private function getCommissionable($line)
    {
        // non-personal-volume doesn't get sent over to the commission engine.
        // if commissionable was 'false' it still gets counted towards personal volume, but you don't get commission when it is resold

        // Starter kits don't count toward personal volume.
        if (isset($line->bundle_id)) {
            $bundle = $line->bundle()->withTrashed()->first();
            if ($bundle->starter_kit) {
                if (app('globalSettings')->getGlobal('comm_engine_starter_kits', 'show')) {
                    return 'true';
                }
                return 'non-personal-volume';
            }
        }
        // Non Resellable Products don't count towards personal volume.
        if (isset($line->item_id) and $line->item()->withTrashed()->first()->product()->withTrashed()->first()->type_id === 6) {
            return 'non-personal-volume';
        }
        // A Rep's own created product doesn't count towards personal volume.
        if (isset($line->item_id) and $line->item()->withTrashed()->first()->product()->withTrashed()->first()->user_id !== config('site.apex_user_id')) {
            return 'non-personal-volume';
        }
        return 'true';
    }

    /**
     * Returns the inventory type based on what the commission engine expects.
     * withTrashed() needs to be used because of backfill and records in a model might have been deleted.
     *
     * @param Order $order
     * @param Orderline $orderline
     * @return int
     */
    private function getInventoryType($order, $orderline)
    {
        $none = 0;
        $wholesale = 1;
        $retail = 2;
        $cashAndCarry = 3;
        $fulfilledByCorporate = 4;
        $affiliate = 5;
        switch ($order->type_id) {
            case 1: // Corporate to Rep
                if (isset($orderline->bundle_id) and $orderline->bundle()->withTrashed()->first()->type_id === 2) {
                    return $fulfilledByCorporate;
                }
                return $wholesale;
            case 3: // Rep to Customer
                // A rep that has been transferred Fulfilled by Corporate Product cannot receive a commission.
                if ($orderline->item()->withTrashed()->first()->product()->withTrashed()->first()->type_id === 5 or $orderline->item()->withTrashed()->first()->product()->withTrashed()->first()->user_id !== config('site.apex_user_id')) {
                    return $none;
                }
                return $retail;
            case 6: // Fulfilled by Corporate
                return $fulfilledByCorporate;
            case 7: // Mixed
                if ($orderline->inventory_owner_id !== config('site.apex_user_id')) {
                    return $fulfilledByCorporate;
                }
                return $none;
            case 9: // Affiliate
                return $affiliate;
            default:
                return $none;
        }
    }

    /**
     * Sets the reciept headers for an orderline to be sent to the commission engine.
     * withTrashed() needs to be used because of backfill and records in a model might have been deleted.
     *
     * @param Order $order
     * @param Orderline $orderline
     * @param double $bundlePercentDiscount
     * @return array $headers
     */
    private function getReceiptHeaders($order, $line, $bundlePercentDiscount)
    {
        $productTypeId = $line->item()->withTrashed()->first()->product()->withTrashed()->first()->type_id;
        $headers = [
            'authemail'         => env('COMM_EMAIL'),
            'apikey'            => env('COMM_API_KEY'),
            'systemid'          => env('COMM_SYSTEM_ID'),
            'qty'               => (int) $line->quantity,
            'commissionable'    => $this->getCommissionable($line),
            'invtype'           => $this->getInventoryType($order, $line),
            'metadata'          => $order->receipt_id,
            'producttype'       => $productTypeId
        ];
        if ($order->type_id === 1) {
            $headers['command'] = 'addreceiptbulk';
            if ($productTypeId === 5 or $productTypeId === 6) {
                $headers['receiptid'] = $this->getReceiptId($line->item_id, config('site.apex_user_id'));
            } else {
                $headers['receiptid'] = $this->getReceiptId($line->item_id, $order->customer_id);
            }
            $headers['wholesaledate'] = $order->created_at->format('Y-m-d H:i:s');
            $headers['wholesaleprice'] = $this->floorDec($this->getAmount($order, $line, $bundlePercentDiscount), 4);
            $headers['userid'] = $order->customer_id;
        } elseif ($order->type_id === 9) {
            $headers['command'] = 'addreceiptbulk';
            $headers['receiptid'] = $this->getReceiptId($line->item_id, config('site.apex_user_id'));
            $headers['wholesaledate'] = $order->created_at->format('Y-m-d H:i:s');
            $headers['wholesaleprice'] = $this->floorDec($line->item()->withTrashed()->first()->wholesalePrice()->first()->price, 4);
            $headers['retailprice'] = $this->floorDec($this->getAmount($order, $line, $bundlePercentDiscount), 4);
            $headers['retaildate'] = $order->created_at->format('Y-m-d H:i:s');
            $headers['userid'] = $order->store_owner_user_id;
        } else {
            $headers['command'] = 'updatereceiptbulk';
            $headers['retailprice'] = $this->floorDec($this->getAmount($order, $line, $bundlePercentDiscount), 4);
            $headers['retaildate'] = $order->created_at->format('Y-m-d H:i:s');
            $headers['receiptid'] = $this->getReceiptId($line->item_id, $order->store_owner_user_id);
            if ($line->item()->withTrashed()->first()->product()->withTrashed()->first()->type_id === 5) {
                $headers['userid'] = $line->inventory_owner_id;
            } else {
                $headers['userid'] = $order->store_owner_user_id;
            }
        }
        return $headers;
    }

    /**
     * Sets the headers to a negitive amount because of a discount.
     * receiptid -1 is for coupon. reciptid -2 is for other.
     *
     * @param Order $order
     * @return array $headers
     */
    public function getNegativeReceiptHeaders($order)
    {
        $headers = [
            'command'           => 'addreceiptbulk',
            'authemail'         => env('COMM_EMAIL'),
            'apikey'            => env('COMM_API_KEY'),
            'systemid'          => env('COMM_SYSTEM_ID'),
            'qty'               => 1,
            'metadata'          => $order->receipt_id,
            'receiptid'         => (count($order->coupons()) > 0 ? -1: -2),
        ];
        if ($order->type_id === 1) {
            $headers['userid'] = $order->customer_id;
            $headers['wholesaledate'] = $order->created_at->format('Y-m-d H:i:s');
            $headers['wholesaleprice'] = $order->total_discount * -1;
        } else {
            $headers['userid'] = $order->store_owner_user_id;
            $headers['retaildate'] = $order->created_at->format('Y-m-d H:i:s');
            $headers['retailprice'] = $this->floorDec($order->total_discount, 4) * -1;
            $headers['wholesaledate'] = $order->created_at->format('Y-m-d H:i:s');
            $_settings = app('globalSettings');
            $headers['wholesaleprice'] = ($order->total_discount * $_settings->getGlobal('discount_wholesale_percent', 'value')) * -1;
        }
        $commissionable = 'non-personal-volume';
        $invtype = 0;
        foreach ($order->lines as $line) {
            if (isset($line->item_id)) {
                if ($commissionable === 'non-personal-volume') {
                    $commissionable = $this->getCommissionable($line);
                }
                if (isset($line->item_id)) {
                    $newInvtype = $this->getInventoryType($order, $line);
                    if ($invtype === 1 and $newInvtype === 4) {
                        $invtype = 1;
                    } else {
                        $invtype = $newInvtype;
                    }
                }
            }
        }
        $headers['commissionable'] = $commissionable;
        $headers['invtype'] = $invtype;
        return $headers;
    }

    /**
     * Sets the reciept id for the commission engine.
     * This ended up being the inventory id becuase the receipts in the commission
     * engine need to tie together between buying wholesale and selling retail.
     * TODO: A custom order doesn't transfer inventory, so a Rep might have purchased something that doesn't have an inventory id.
     * It just sends corporates inventory id instead, because it can't be null. This will fix itself when a custom order transfers inventory.
     *
     * @param int $itemId
     * @param int $userId
     * @return int $inventoryId
     */
    private function getReceiptId($itemId, $userId)
    {
        $inventory = Inventory::where('item_id', $itemId)->where('user_id', $userId)->withTrashed()->first();
        if (!isset($inventory)) {
            $inventory = Inventory::where('item_id', $itemId)->where('user_id', config('site.apex_user_id'))->withTrashed()->first();
        }
        return $inventory->id;
    }

    /**
     * Gets the signup date of a user. If it is a rep it will use the created at date for a subscription because
     * a rep might have been a customer before they became a rep.
     *
     * @param User $user
     * @return string $date
     */
    private function getSignupDate($user)
    {
        if (isset($user->subscriptions)) {
            return $user->subscriptions->created_at->format('Y-m-d');
        }
        return $user->created_at->format('Y-m-d');
    }

    /**
     * Gets the user type based on the commission engine types.
     * It is based only on if the user gets commissions or not.
     *
     * @param User $user
     * @return int
     */
    private function getUserType($user)
    {
        if ($user->role_id == 5) {
            return 1; // affiliate: users that get commissions
        }
        return 2; // customer: users that don't get commissions
    }

    /**
     * Returns a status id depending on what happend to an Order/User
     * when it was sent to the commission engine.
     *
     * @param string $stautName
     * @return int
     */
    public function getCommissionStatusId($statusName)
    {
        switch ($statusName) {
            case 'uncommitted':
                return 1;
            case 'committed':
                return 2;
            case 'non-commissionable':
                return 3;
            case 'error':
                return 4;
            case 'backfill':
                return 5;
            case 'queued':
                return 6;
            default:
                return 1;
        }
    }

    /**
     * Returns a decimal with a precision without rounding. Php is acutually slightly inaccurate doing this.
     * https://stackoverflow.com/questions/12277945/php-how-do-i-round-down-to-two-decimal-places
     *
     * @param double $value
     * @param int $precision
     * @return float
     */
    private function floorDec($value, $precision = 2)
    {
        if ($precision < 0) {
            $precision = 0;
        }
        $numPointPosition = intval(strpos($value, '.'));
        if ($numPointPosition === 0) { // $value is an integer
            return $value;
        }
        return floatval(substr($value, 0, $numPointPosition + $precision + 1));
    }
}
