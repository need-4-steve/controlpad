<?php

namespace App\Http\Controllers\Api\V2\MCommServices;

use App\Services\Commission\CommissionService;
use App\Http\Controllers\Api\V2\MCommController;
use App\Models\Inventory;
use App\Services\Settings\SettingsService;
use GuzzleHttp;
use App\Models\User;
use App\Models\Order;
use App\Models\Orderline;
use App\Jobs\SendCommEngineUser;
use App\Jobs\SendCommEngineOrder;
use App\Http\Controllers\Api\V2\MCommHelpers\Dispatcher;
use App\Http\Controllers\Api\V2\MCommHelpers\MemoryCache;
use Carbon\Carbon;
use App\Services\Commission\CommissionServiceInterface;

class MCommCommissionService implements CommissionServiceInterface
{
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
                $requestBody=[
                    "companyordernum"   =>  $order->transaction_id,
                    "companyaid"        =>  $order->store_owner_user_id,
                    "orderdate"         =>  date('Y-m-d H:i:s',strtotime($order->created_at)),
                    "period"            =>  $period,
                    "totalamount"       =>  $order->total_price,
                    "shipamount"        =>  $order->total_shipping,
                    "discount"          =>  $order->total_discount,
                    "taxamount"         =>  $order->total_tax,
                    "bv"                =>  $order->subtotal_price,
                    "cv"                =>  $order->subtotal_price,
                    "currency"          =>  "USD",
                    "country"           =>  "US",
                    "state"             =>  User::where('id',$order->store_owner_user_id)->first()->addresses->where('label', 'Shipping')->first()->state,
                    "ordertype"         =>  100003,
                    "status"            =>  8,
                    "custom1"           =>  $order->type_id,
                    "custom2"           =>  "",
                    "custom3"           =>  "",
                    "custom4"           =>  "",
                    "custom5"           =>  "",
                ];
                $orderlines=[];
                $lineCount=0;
                foreach ($order->lines as $line) {
                    if (isset($line->item_id) and !$line->in_comm_engine) {
                        $orderlines[]=[
                            "itemcode"  =>  $line->item_id,
                            "orderline" =>  ++$lineCount,
                            "unitprice" =>  $line->price,
                            "qty"       =>  $line->quantity,
                            "tax"       =>  $line->price / $order->subtotal_price * $line->price,
                            "bv"        =>  $order->subtotal_price,
                            "cv"        =>  $order->subtotal_price,
                            "custom1"   =>  "Line $lineCount",
                            "custom2"   =>  "",
                            "custom3"   =>  "",
                            "custom4"   =>  "",
                            "custom5"   =>  ""
                        ];
                    }
                }
                $requestBody['orderline']=$orderlines;
                $responseData = $apiClient->request('PUT', MCommController::MCOMM_BASEURL.'/v1/order',MCommController::USER_AND_ORDER_REQUEST_HEADERS,$requestBody);
                $order->update(['comm_engine_status_id' => 8]); // Order has been cancelled in the commission engine.
                return $responseData;
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

    /**
     * addUser
     *
     * @param  mixed $user
     * @param  mixed $initialize
     *
     * @return void
     */
    public function addUser($user=null, $initialize = false)
    {
        $apiClient = new Dispatcher();
        $thisUser=is_null($user) ? \Auth::user() : $user;
        try {
            $_settings = app('globalSettings');
            if ($_settings->getGlobal('use_commission_engine', 'value') or $initialize == true) {
                $sponsor = User::where('id', $user->sponsor_id)->withTrashed()->first();
                if (isset($sponsor->comm_engine_status_id) and $sponsor->comm_engine_status_id !== 2) {
                    $this->addUser($sponsor, true);
                }
                $apiClient = new Dispatcher();
                $requestBody=[
                    "email"				=> $thisUser->email,
                    "signupdate"		=> isset($thisUser->subscriptions) ? $thisUser->subscriptions->created_at->format('Y-m-d H:i:s') : $thisUser->created_at->format('Y-m-d H:i:s'),
                    "apfirstname"		=> $thisUser->first_name,		
                    "aplastname"		=> $thisUser->last_name,
                    "companyaid"		=>  $thisUser->id,	
                    "associatetype"		=> 2, // what is associatetype?
                    "recognitionname"	=> 	$thisUser->getFullNameAttribute(),
                    "website" 			=> 	$thisUser->hasRole('Rep') ? env('REP_URL') : env('APP_URL'),
                    "country"			=> "US",
                    "status"			=> 1,
                    "referredby"		=> $thisUser->sponsor_id,
                    "placementid"		=> $thisUser->sponsor_id,
                    "side"				=> -1,
                    "custom1"			=> $user->number,
                    "custom2"			=> "Custom 2",
                    "custom3"			=> "Custom 3",
                    "custom4"			=> "Custom 4",
                    "custom5"			=> "Custom 5"
                ];
                $data = $apiClient->request('PUT', MCommController::MCOMM_BASEURL.'/v1/user',MCommController::USER_REQUEST_HEADERS,$requestBody);
                $data['result']=$data['result']==0 ? 'SUCCESS' : 'FAILURE';
                $user->update(['comm_engine_status_id' => 2]);
                return response()->json($data, 1);
            }
        } catch (\Exception $e) {
            $user->update(['comm_engine_status_id' => 4]);
            if ($initialize == false) {
                logger()->warning('MCommCommission Engine: error in sending a user', [
                    'message' => $e->getMessage(),
                    'user' => ['id' => $user->id, 'name' => $user->first_name.' '.$user->last_name, 'email' => $user->email],
                    'fingerprint' => 'MCommCommissionService addUser',
                ]);
            }
        }
    }

    /**
     * findUser
     *
     * @param  mixed $user
     *
     * @return void
     */
    public function findUser($user){
        $compareID=$user->id;
        $apiClient = new Dispatcher();
        $requestBody=[
			'template' 		=> 	'mca_Members',
			'requestinfo'	=> 	'true',
			'onlycount' 	=> 	'false',
			'skip' 			=> 	'0',
			'take' 			=> 	'50',
			'key' 			=> 	'878978978978',
			'companyaid' 	=>	-1, //test values
		];
        $data=$apiClient->request('POST', self::MCOMM_BASEURL.'/gr/data/',array_merge(self::REPORT_REQUEST_HEADERS,['clearcache_x'=>'true']),$requestBody);
		foreach ($data['data'] as $userData){
            if ($userData['companyaid']==$compareID){
                return true;
            }
        }
		return false;
    }

    /**
     * addReceipt
     *
     * @param  mixed $order
     * @param  mixed $initialize
     *
     * @return void
     */
    public function addReceipt($order=null, $initialize = false)
    {
        $line = null;
        // for testing
        if (is_null($order)) $order= Order::where('comm_engine_status_id', 1)->first();    
        try {
            $memCache= new MemoryCache();
            $period = $memCache->exists(MCommController::getMemCacheKey()) ? json_decode($memCache->read(MCommController::getMemCacheKey()),true)['period'] : 0;
            $apiClient = new Dispatcher();
            if (empty($period)){
                $requestBody=['template' => 'mca_Periods','requestinfo' => 'true','onlycount' => 'false','skip' => '0','take' => '50','key' => '878978978978','status' => '2'];
                $data = $apiClient->request('POST', MCommController::MCOMM_BASEURL.'/gr/data/',array_merge(MCommController::REPORT_REQUEST_HEADERS,['clearcache_x'=>'true']),$requestBody);
                $period = $data['data'][0]['period'];
                $memCache= new MemoryCache((MCommController::getMemCacheKey()));
                $memCache->write(json_encode(['periodName'=>$periodName,'period'=>$period]));
            }
            $requestBody=[
                "companyordernum"   =>  $order->transaction_id,
                "companyaid"        =>  $order->store_owner_user_id,
                "orderdate"         =>  date('Y-m-d H:i:s',strtotime($order->created_at)),
                "period"            =>  $period,
                "totalamount"       =>  $order->total_price,
                "shipamount"        =>  $order->total_shipping,
                "discount"          =>  $order->total_discount,
                "taxamount"         =>  $order->total_tax,
                "bv"                =>  $order->subtotal_price,
                "cv"                =>  $order->subtotal_price,
                "currency"          =>  "USD",
                "country"           =>  "US",
                "state"             =>  User::where('id',$order->store_owner_user_id)->first()->addresses->where('label', 'Shipping')->first()->state,
                "ordertype"         =>  100003,
                "status"            =>  $order->comm_engine_status_id,
                "custom1"           =>  $order->type_id,
                "custom2"           =>  "",
                "custom3"           =>  "",
                "custom4"           =>  "",
                "custom5"           =>  "",
            ];
            $orderlines=[];
            $lineCount=0;
            foreach ($order->lines as $line) {
                if (isset($line->item_id) and !$line->in_comm_engine) {
                    $orderlines[]=[
                        "itemcode"  =>  $line->item_id,
                        "orderline" =>  ++$lineCount,
                        "unitprice" =>  $line->price,
                        "qty"       =>  $line->quantity,
                        "tax"       =>  $line->price / $order->subtotal_price * $line->price,
                        "bv"        =>  $order->subtotal_price,
                        "cv"        =>  $order->subtotal_price,
                        "custom1"   =>  "Line $lineCount",
                        "custom2"   =>  "",
                        "custom3"   =>  "",
                        "custom4"   =>  "",
                        "custom5"   =>  ""
                    ];
                }
            }
            $requestBody['orderline']=$orderlines;
            $responseData = $apiClient->request('PUT', MCommController::MCOMM_BASEURL.'/v1/order',MCommController::USER_AND_ORDER_REQUEST_HEADERS,$requestBody);
            $_settings = app('globalSettings');
            if ($_settings->getGlobal('use_commission_engine', 'value')
            and $this->checkCommissionableOrderType($order)
            or $initialize == true
            and $this->checkCommissionableOrderType($order)) {
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
                        Orderline::where('id', $line->id)->update(['in_comm_engine' => true]);
                        $inventory->update(['quantity_imported' => 0]);
                    } elseif (isset($line->bundle_id) and !isset($line->item_id)) {
                        $bundle = $line->bundle()->withTrashed()->first();
                        $bundlePercentDiscount[$bundle->id] = $this->getBundleDiscountPercent($bundle, $line->price);
                        Orderline::where('id', $line->id)->update(['in_comm_engine' => true]);
                    }
                }
                $order->update(['comm_engine_status_id' => 2]);
            } elseif ($_settings->getGlobal('use_commission_engine', 'value') or $initialize) {
                $order->update(['comm_engine_status_id' => 3]);
            }
            return $responseData;
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'retcount == 0') !== false) {
                $order->update(['comm_engine_status_id' => 9]);
            } else {
                // error while sending over to commission engine
                if (is_null($order)) echo "null order";
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

}