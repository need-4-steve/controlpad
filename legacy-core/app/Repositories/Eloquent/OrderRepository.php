<?php

namespace App\Repositories\Eloquent;

use Auth;
use DB;
use Cache;

use Carbon\Carbon;
use App\Models\Address;
use App\Models\CommissionReceipt;
use App\Models\Product;
use App\Models\Order;
use App\Models\Orderline;
use App\Models\OrderType;
use App\Models\Cartline;
use App\Models\User;
use App\Models\UserSetting;
use App\Models\Cart;
use App\Models\CashType;
use App\Models\Item;
use App\Services\Inventory\InventoryService;
use App\Services\PayMan\PayManService;
use App\Repositories\Eloquent\Traits\CommonCrudTrait;
use App\Repositories\Contracts\OrderRepositoryContract;
use App\Repositories\Eloquent\CouponAppliedRepository;
use App\Repositories\Eloquent\AuthRepository;
use App\Repositories\Eloquent\UserSettingsRepository;

class OrderRepository implements OrderRepositoryContract
{
    use CommonCrudTrait;

    protected $cartRepo;
    protected $userRepo;
    protected $inventoryService;
    protected $userSettingsRepo;


    public function __construct(
        CartRepository $cartRepo,
        UserRepository $userRepo,
        CouponAppliedRepository $couponAppliedRepo,
        AuthRepository $authRepo,
        InventoryService $inventoryService,
        UserSettingsRepository $userSettingsRepo,
        PayManService $payMan
    ) {
        $this->inventoryService = $inventoryService;
        $this->cartRepo = $cartRepo;
        $this->userRepo = $userRepo;
        $this->couponAppliedRepo = $couponAppliedRepo;
        $this->authRepo = $authRepo;
        $this->userSettingsRepo = $userSettingsRepo;
        $this->paymentManager = $payMan;
    }

    public function buildOrderIndexQuery($request)
    {
        if (auth()->user()->hasRole(['Superadmin', 'Admin'])) {
            $user_id =  config('site.apex_user_id');
        } else {
            $user_id = auth()->id();
        }

        $timezone = $this->userSettingsRepo->getUserTimeZone($user_id);
        $request['start_date'] = Carbon::parse($request['start_date'], $timezone)->startOfDay()->setTimezone('UTC')->format('Y-m-d H:i:s');
        $request['end_date'] = Carbon::parse($request['end_date'], $timezone)->endOfDay()->setTimezone('UTC')->format('Y-m-d H:i:s');

        $requestData = $this->prepareOrderRequest($request);
        $orders = Order::with('orderType', 'shippingAddress.geolocation', 'storeOwner.businessAddress', 'storeOwner.phone', 'customer.phone', 'shippingAddress', 'shipment')
                ->select('orders.*', 'first_name as customer_first_name', 'last_name as customer_last_name')
                ->join('users', 'users.id', '=', 'customer_id');
        if ($requestData['store_owner_user_id'] !== 'All') {
            if ($requestData['store_owner_user_id'] === config('site.apex_user_id')) {
                $orders->whereHas('storeOwner', function ($query) use ($requestData) {
                    $query->where('type_id', 9)
                        ->orWhere('store_owner_user_id', $requestData['store_owner_user_id']);
                });
            } else {
                $orders = $orders->where('store_owner_user_id', $requestData['store_owner_user_id'])->where('type_id', '!=', 9);
            }
        }

        if ($requestData['type'] !== null) {
            $orders->where('type_id', $requestData['type']);
        }

        if ($requestData['customer_id'] !== null) {
            $orders->where('customer_id', $requestData['customer_id']);
        }

        if (isset($requestData['status'])) {
            $orders->where('orders.status', $requestData['status']);
        }
        $orders->whereBetween('orders.created_at', [$requestData['start_date'], $requestData['end_date']]);
        if (! empty($requestData['search_term'])) {
            $orders->where(function ($query) use ($requestData) {
                $query->where('orders.receipt_id', 'LIKE', '%' . $requestData['search_term'] . '%')
                ->orWhere('users.first_name', 'LIKE', '%' . $requestData['search_term'] . '%')
                ->orWhere('users.last_name', 'LIKE', '%' . $requestData['search_term'] . '%');
            });
        }

        $orders->orderBy($requestData['column'], $requestData['order']);
        return $orders;
    }

    public function index($request)
    {
        return $this->buildOrderIndexQuery($request)
                ->orderBy($request['column'], $request['order'])
                ->paginate($request['per_page']);
    }

    public function findByReceiptId($receipt_id, $relationships = [])
    {
        $order = Order::with($relationships)->where('receipt_id', $receipt_id)->first();
        return $order;
    }

    public function findOrderByUser($user_id, $receipt_id, $relationships = [])
    {
        $order = Order::with($relationships)
            ->where('receipt_id', $receipt_id)
            ->where('customer_id', $user_id)
            ->orWhere('receipt_id', $receipt_id)
            ->where('store_owner_user_id', $user_id)
            ->first();
        return $order;
    }

    /**
     *  Grab an order's billing address, shipping address, phone numbers, etc.
     *
     * @param  array $data
     * @return array $phones [$billingAddress, $shippingAddress, $phones]
     */
    public function getBillingDetails()
    {
        $billingAddress = null;
        $shippingAddress = null;
        $phone = null;
        $user = null;

        if (session()->get('lead') !== null) {
            $user = session()->get('lead');
        } else {
            // If lead isn't in session then grab user details,
            // addresses and phones through eager loading
            $authedUser = Auth::user();
            if (!empty($authedUser)) {
                $authedUser = $authedUser->load('addresses', 'phone');
                $billingAddress = $authedUser->addresses->where('label', 'Billing')->first();
                $shippingAddress = $authedUser->addresses->where('label', 'Shipping')->first();
                $user = $authedUser;
            }
        }

        return compact(
            'billingAddress',
            'shippingAddress',
            'phone',
            'user'
        );
    }

    public function create(array $data, int $store_owner_user_id = null, $orderTypeId = null)
    {
        //get the store info
        if ($store_owner_user_id == null) {
            if (!$store_owner_user_id = $data['cart']['store_owner_id']) {
                if ($store_owner_user_id = session('store_owner.id')) {
                    $store_owner_user_id = $store_owner_user_id;
                } else {
                    $store_owner_user_id = config()->get('site.apex_user_id');
                }
            }
        }
        // find the cart to prevent session injection
        $cart = Cart::with('lines', 'shipping', 'coupons', 'bundles.wholesalePrice')
                    ->where('id', $data['cart']['id'])
                    ->first();
        $total_cost = 0;
        foreach ($data['cart']['lines'] as $line) {
            $total_cost += $line['price'] * $line['quantity'];
        }
        if (isset($cart->bundles)) {
            foreach ($cart->bundles as $bundle) {
                $total_cost += $bundle->pivot->quantity * $bundle->wholesalePrice->price;
            }
        }
        $discount = $total_cost - $data['cart']['subtotal_price'];
        // set order fields
        $fields = [
            'store_owner_user_id',
            'receipt_id',
            'total_price',
            'shipping_rate_id',
            'subtotal_price',
            'total_tax',
            'total_shipping',
            'total_discount',
            'paid_at',
            'cash',
            'payment_type'
        ];
        // Get customer and store owner pids for new checkout data
        $buyerId = $data['cart']['user_id'];
        $users = User::select('id', 'pid', 'email', 'first_name', 'last_name')
            ->whereIn('id', [$buyerId, $store_owner_user_id])->get()->keyBy('id');
        // create a new order
        $order = new Order;
        $order->pid = \CPCommon\Pid\Pid::create();
        foreach ($fields as $field) {
            $order->$field = array_get($data['cart'], $field, '');
        }
        if (isset($order->cash) && filter_var($order->cash, FILTER_VALIDATE_BOOLEAN)) {
            $order->payment_type = 'cash';
        } else {
            $order->payment_type = 'credit-card';
        }
        if (isset($data['cashType'])) {
            $order->cash_type = $data['cashType'];
        }
        if (isset($data['cart']['tax_invoice_pid'])) {
            $order->tax_invoice_pid = $data['cart']['tax_invoice_pid'];
        }
        $order->customer_id = $buyerId;
        if (isset($users[$buyerId])) {
            $order->buyer_pid = $users[$buyerId]->pid;
            $order->buyer_email = $users[$buyerId]->email;
        }
        if (isset($users[$store_owner_user_id])) {
            $order->seller_pid = $users[$store_owner_user_id]->pid;
            $order->seller_name = $users[$store_owner_user_id]->full_name;
        }
        $order->buyer_first_name = $data['user']['first_name'];
        $order->buyer_last_name = $data['user']['last_name'];
        $order->store_owner_user_id = $store_owner_user_id;
        $order->paid_at = Carbon::Now()->toDateTimeString();

        if (!isset($orderTypeId)) {
            $order->type_id = $this->orderType(
                $order->store_owner_user_id,
                $order->customer_id
            );
        } else {
            $order->type_id = $orderTypeId;
        }

        $order->total_discount = $discount;
        $order->save();

        if (isset($cart->coupons) && count($cart->coupons) > 0) {
            $coupon = $cart->coupons->first();
            $this->couponAppliedRepo->attachOrder($order, $coupon);
            $coupon->uses++;
            $coupon->save();
            $cart->coupons()->detach();
            $order->coupon_id = $coupon->id;
            $order->save();
        }
        if (! empty($data['addresses'])) {
            if (!isset($data['payment']) || $data['payment'] == null || !isset($data['payment']['name'])) {
                $data['payment']['name'] = $data['user']['first_name'] . ' ' . $data['user']['last_name'];
                $data['addresses']['billing'] = $data['addresses']['shipping'];
            }
            // save billing address
            $billingAddress = $data['addresses']['billing'];
            $billingAddress['name'] = $data['payment']['name'];
            $billingAddress['label'] = 'Billing';

            $this->saveAddress($billingAddress, $order->id);
            Order::where('id', $order->id)
                ->update(['billing_address' => json_encode($this->convertAddressForCheckoutApi($billingAddress))]);
            // save shipping address
            $shippingAddress = $data['addresses']['shipping'];
            $shippingAddress['label'] = 'Shipping';
            if (!isset($shippingAddress['name']) || $shippingAddress['name'] == null) {
                $shippingAddress['name'] = $data['user']['first_name'] . ' ' . $data['user']['last_name'];
            }
            if (count($shippingAddress) <= 2) {
                // if we have only one field, copy billing address
                $shippingAddress = $billingAddress;
                if ($shippingAddress['name'] == null) {
                    $shippingAddress['name'] = $data['user']['first_name'] . ' ' . $data['user']['last_name'];
                }
                $this->saveAddress($shippingAddress, $order->id);
            } else {
                $this->saveAddress($shippingAddress, $order->id);
            }
            Order::where('id', $order->id)
                ->update(['shipping_address' => json_encode($this->convertAddressForCheckoutApi($shippingAddress))]);
        }

        return $order;
    }

    /**
     *  Save an order's address
     *
     * @param  array $data, integer $orderId
     * @return Address $address
     */
    public function saveAddress($data, $orderId)
    {
        $address = new Address;
        $address->address_1 = $data['address_1'];
        if (isset($data['address_2'])) {
            $address->address_2 = $data['address_2'];
        }
        $address->city = $data['city'];
        $address->state = $data['state'];
        $address->zip = $data['zip'];
        $address->label = $data['label'];
        $address->addressable_type = 'App\Models\Order';
        $address->addressable_id = $orderId;
        $address->name = $data['name'];
        $address->save();
        return $address;
    }

    private function convertAddressForCheckoutApi($address)
    {
        return [
            'name' => (isset($address['name']) ? $address['name'] : null),
            'email' => (isset($address['email']) ? $address['email'] : null),
            'line_1' => $address['address_1'],
            'line_2' => (isset($address['address_2']) ? $address['address_2'] : null),
            'city' => $address['city'],
            'state' => $address['state'],
            'zip' => $address['zip'],
        ];
    }

    public function genReceiptID()
    {
        return "O" . strtoupper(str_random(5));
    }

    /**
     * undocumented function summary
     *
     * Undocumented function long description
     *
     * @param type var Description
     * @return {11:return type}
     */
    public function orderTypes()
    {
        return OrderType::all();
    }

    /**
     * Get all orders between given dates
     *
     * @param string $startDate Beginning date (time will be set to 12:00 AM)
     * @param string $endDate   Ending date (time will be set to 11:59 PM)
     * @param array  $relations Related models to load
     * @return mixed
     */
    public function getByDate($startDate, $endDate, array $relations = [])
    {
        if (Auth::user()->hasRole(['Admin'])) {
            return Order::with($relations)
                    ->where('store_owner_user_id', config('site.apex_user_id'))
                    ->where('status', '!=', 'cancelled')
                    ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                    ->orderBy('created_at')
                    ->get();
        } else {
            return Order::with($relations)
                    ->where('store_owner_user_id', Auth::user()->id)
                    ->where('status', '!=', 'cancelled')
                    ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                    ->orderBy('created_at')
                    ->get();
        }
    }
    /**
     * Get all sales this includes for orders and for orderlines
     *
     * @param array $request This request is the pagination info.
     * @param int $this is the user id
     * @return sales
     */
    public function sales($request, $store_owner_id)
    {
        $sales = Order::with('orderType')
                ->select('orders.*', 'first_name as customer_first_name', 'last_name as customer_last_name')
                ->join('users', 'users.id', '=', 'customer_id')
                ->whereBetween('orders.created_at', [$request['start_date'], $request['end_date']])
                ->where('type_id', '!=', 8)
                ->orderBy($request['column'], $request['order']);
        if (isset($request['search_term'])) {
            $sales->search($request['search_term'], ['customer.first_name', 'customer.last_name', 'receipt_id', 'id', 'gateway_reference_id', 'transaction_id']);
        }

        if (auth()->user()->hasRole(['Superadmin','Admin'])) {
            $sales->where('store_owner_user_id', $store_owner_id);
        } elseif (auth()->user()->hasRole(['Rep'])) { //use to be hasSellerType(['Affiliate'])
            $sales->whereIn('type_id', [3, 4, 9])
                ->where('store_owner_user_id', $store_owner_id);
        } else {
            $sales->whereHas('lines', function ($query) use ($store_owner_id) {
                $query->where('inventory_owner_id', $store_owner_id);
            })
            ->with(['lines' => function ($query) use ($store_owner_id) {
                $query->where('inventory_owner_id', $store_owner_id);
            }]);
        }
        return $sales->paginate($request['per_page']);
    }

    public function getOrderTotalsByDate($startDate, $endDate)
    {
        if (Auth::user()->hasRole(['Superadmin','Admin'])) {
            $selectString = 'DATE(created_at) AS order_date, SUM(subtotal_price) AS subtotal, ';
            $selectString .= 'COUNT(id) AS order_count, store_owner_user_id';
            return DB::table('orders')->select(
                DB::raw($selectString)
            )->where('store_owner_user_id', config('site.apex_user_id'))
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->groupBy('order_date')->orderBy('order_date', 'DESC')->get();
        } else {
            $selectString = 'DATE(created_at) AS order_date, SUM(subtotal_price) AS subtotal, ';
            $selectString .= 'COUNT(id) AS order_count, store_owner_user_id';
            return DB::table('orders')->select(
                DB::raw($selectString)
            )->where('store_owner_user_id', Auth::user()->id)
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->groupBy('order_date')->orderBy('order_date', 'DESC')->get();
        }
    }

    /**
     * Find an order by transaction id
     *
     * @param string $transactionId
     * @param array $eagerLoad
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function byTransactionId($transactionId, array $eagerLoad = [])
    {
        return Order::with($eagerLoad)->where('transaction_id', $transactionId)->first();
    }

    /**
     * Generate the order type of an order based on seller/buyer
     *
     * @param int $storeOwnerId, int $customerId
     * @return int
     */
    public function orderType($storeOwnerId, $customerId)
    {
        $storeOwnerRole = User::with('role')->where('id', $storeOwnerId)->first()->role->name;
        $customerRole = User::with('role')->where('id', $customerId)->first()->role->name;

        switch ([$storeOwnerRole, $customerRole]) {
            case ['Superadmin', 'Rep']:
                return 1;
            case ['Admin', 'Rep']:
                return 1;
            case ['Superadmin', 'Customer']:
                return 2;
            case ['Admin', 'Customer']:
                return 2;
            case ['Rep', 'Customer']:
                return 3;
            case ['Rep', 'Rep']:
                return 3; // We are not going to use rep to rep at this time.
            case ['Admin', 'Admin']:
                return 5;
            case ['Admin', 'Superadmin']:
                return 5;
            case ['Superadmin', 'Admin']:
                return 5;
            case ['Superadmin', 'Superadmin']:
                return 5;
            default:
                return 0;
        }
    }

    /**
     *
     */
    public function newOrderCount($storeOwnerId)
    {
        $timeZone = $this->getUserTimeZone($storeOwnerId);
        $startDay = Carbon::now($timeZone)->startOfDay()->setTimezone('UTC')->toDateTimeString();
        $endDay = Carbon::now($timeZone)->endOfDay()->setTimezone('UTC')->toDateTimeString();
        if (auth()->user()->hasRole(['Superadmin','Admin'])) {
            $orderTypes = [1,2];
        } else {
            $orderTypes = [3,9];
        }
        $newOrderCount = Order::where('status', 'unfulfilled')
            ->where('store_owner_user_id', $storeOwnerId)
            ->whereIn('type_id', $orderTypes)
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$startDay, $endDay])
            ->count();

        $unshippedOrderCount = Order::where('status', 'unfulfilled')
            ->where('store_owner_user_id', $storeOwnerId)
            ->whereIn('type_id', $orderTypes)
            ->where('status', '!=', 'cancelled')
            ->count();

        return [
            'total' => $newOrderCount,
            'unshipped' => $unshippedOrderCount
        ];
    }

    public function orderVolume($storeOwnerId)
    {
        $timeZone = $this->getUserTimeZone($storeOwnerId);
        $timeDay = Carbon::now($timeZone)->startOfDay()->setTimezone('UTC')->toDateTimeString();
        $timeMonth =Carbon::now($timeZone)->startOfMonth()->setTimezone('UTC')->toDateTimeString();
        if (auth()->user()->hasRole(['Superadmin','Admin'])) {
            $orderTypes = [1,2];
        } else {
            $orderTypes = [3,9];
        }
        $todaysOrderVolume = Order::where('created_at', '>=', $timeDay)
                                ->whereIn('type_id', $orderTypes)
                                ->where('store_owner_user_id', $storeOwnerId)
                                ->where('status', '!=', 'cancelled')
                                ->count();

        $monthlyOrderVolume = Order::where('created_at', '>=', $timeMonth)
                                ->whereIn('type_id', $orderTypes)
                                ->where('store_owner_user_id', $storeOwnerId)
                                ->where('status', '!=', 'cancelled')
                                ->count();

        return [
            'today' => round($todaysOrderVolume),
            'month' => round($monthlyOrderVolume)
        ];
    }

    public function salesVolume($storeOwnerId)
    {
        $timeZone = $this->getUserTimeZone($storeOwnerId);
        $startDay = Carbon::now($timeZone)->startOfDay()->setTimezone('UTC')->toDateTimeString();
        $endDay = Carbon::now($timeZone)->endOfDay()->setTimezone('UTC')->toDateTimeString();
        $timeMonth =Carbon::now($timeZone)->startOfMonth()->setTimezone('UTC')->toDateTimeString();
        if (auth()->user()->hasRole(['Superadmin','Admin'])) {
            $orderTypes = [1,2];
        } else {
            $orderTypes = [3,9];
        }
        $todaySalesVolume = Order::whereBetween('created_at', [$startDay,$endDay])
                                ->where('store_owner_user_id', $storeOwnerId)
                                ->whereIn('type_id', $orderTypes)
                                ->where('status', '!=', 'cancelled')
                                ->select(DB::raw('SUM(total_price) as totalVolume'))
                                ->first()
                                ->totalVolume;

        $monthlySalesVolume = Order::where('created_at', '>=', $timeMonth)
                                ->where('store_owner_user_id', $storeOwnerId)
                                ->whereIn('type_id', $orderTypes)
                                ->where('status', '!=', 'cancelled')
                                ->select(DB::raw('SUM(total_price) as totalVolume'))
                                ->first()
                                ->totalVolume;

        return [
            'today' => round($todaySalesVolume),
            'month' => round($monthlySalesVolume)
        ];
    }

    public function prepareOrderRequest($request = [])
    {
        $requestString = [
            'customer_id'         => null,
            'store_owner_user_id' => $this->authRepo->getOwnerId(),
            'seller_type'         => $this->authRepo->getSellerType(),
            'type'                => null,
            'status'              => null,
            'start_date'          => Carbon::now()->subDays(30)->toDateTimeString(),
            'end_date'            => Carbon::now()->toDateTimeString(),
            'search_term'         => '',
            'column'              => 'id',
            'order'               => 'DESC',
            'per_page'            => '15'
        ];

        if (isset($request['customer_id']) and is_numeric($request['customer_id'])) {
            $requestString['customer_id'] = $request['customer_id'];
        }

        if (isset($request['store_owner_user_id']) and is_numeric($request['store_owner_user_id'])) {
            $requestString['store_owner_user_id'] = $request['store_owner_user_id'];
        } elseif (isset($request['store_owner_user_id']) and $request['store_owner_user_id'] == 'All') {
            $requestString['store_owner_user_id'] = 'All';
        }

        if (isset($request['type'])) {
            $requestString['type'] = $request['type'];
        }

        if (isset($request['status']) && $request['status'] !== 'all') {
            $requestString['status'] = $request['status'];
        }

        if (isset($request['end_date']) and isValidDateTime($request['end_date']) or
            isset($request['end_date']) and isValidDate($request['end_date'])) {
            $requestString['end_date'] = Carbon::parse($request['end_date']);
        }

        if (isset($request['start_date']) and isValidDateTime($request['start_date']) or
            isset($request['start_date']) and isValidDate($request['start_date'])) {
            $requestString['start_date'] = Carbon::parse($request['start_date']);
        }

        if (isset($request['search_term'])) {
            $requestString['search_term'] = $request['search_term'];
        }

        if (isset($request['column'])) {
            $requestString['column'] = $request['column'];
        }

        if (isset($request['order']) and in_array(strtoupper($request['order']), ['ASC', 'DESC'], true)) {
            $requestString['order'] = strtoupper($request['order']);
        }

        if (isset($request['per_page'])) {
            $requestString['per_page'] = $request['per_page'];
        }
        return $requestString;
    }

    /**
     * Get all rep sales
     *
     * @param array $request This request is the pagination info.
     * @param int $this is the user id
     * @return sales
     */
    public function repSales($request)
    {
        $request = $this->prepareOrderRequest($request);
        $sales = Order::with('orderType')
                ->with('storeOwner')
                ->select('orders.*', 'first_name as store_owner_first_name', 'last_name as store_owner_last_name')
                ->join('users', 'users.id', '=', 'store_owner_user_id')
                ->whereBetween('orders.created_at', [$request['start_date'], $request['end_date']])
                ->where('store_owner_user_id', '!=', config('site.apex_user_id'))
                ->search($request['search_term'])
                ->orderBy($request['column'], $request['order']);

        return $sales->paginate($request['per_page']);
    }

    /**
     * Find all orders by receipt_id
     *
     * @param array $receiptIds receipt_id of orders
     * @param array $eagerLoad
     * @return Order
     */
    public function getOrdersByReceiptId(array $receiptIds = [], array $eagerLoad = [])
    {
        return Order::whereIn('receipt_id', $receiptIds)->with($eagerLoad)->get();
    }

    private function getUserTimeZone($userId)
    {
        $timeZone = UserSetting::where('user_id', $userId)->first();
        if ($timeZone == null) {
            $timeZone = 'UTC';
        } else {
            $timeZone = $timeZone->timezone;
        }

        return $timeZone;
    }

    public function getOrdersByCommissionEngineStatus($statusKeyId, $paginate = false, $cancelled = false)
    {
        $orders = Order::where('comm_engine_status_id', $statusKeyId);
        if ($cancelled) {
            $orders->where('status', '=', 'cancelled');
        } else {
            $orders->where('status', '!=', 'cancelled');
        }
        if ($paginate) {
            return $orders->paginate(50);
        }
        return $orders->get();
    }

    /**
     * retreive orders to make a picklist for a store owner
     *
     * @param Integer
     * @param Array
     * @return Collection
     */
    public function picklist($storeOwnerId, $request)
    {
        // set date formats and timezones
        $timezone = $this->userSettingsRepo->getUserTimeZone($storeOwnerId);
        $startDate = Carbon::parse($request['start_date'], $timezone)->startOfDay()->setTimezone('UTC')->format('Y-m-d H:i:s');
        $endDate = Carbon::parse($request['end_date'], $timezone)->endOfDay()->setTimezone('UTC')->format('Y-m-d H:i:s');

        $orders = Order::with('lines.item.product', 'addresses', 'customer')
            ->with(['lines' => function ($query) {
                $query->join('items', 'orderlines.item_id', '=', 'items.id')
                    ->orderBy('items.location', 'asc');
            }])
            ->where('store_owner_user_id', $storeOwnerId)
            ->where('status', 'unfulfilled')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orWhere('type_id', 9)
            ->where('status', 'unfulfilled')
            ->whereBetween('created_at', [$startDate, $endDate]);

            return $orders->get();
    }

    /**
     * gets gateway reference id to back fill missing one for orders
     *
     */
    public function backfillTransationId()
    {
        $orders = Order::where('gateway_reference_id', null)->get();
        foreach ($orders as $order) {
            if ($order->transaction_id != null || $order->transaction_id != '') {
                $transaction = $this->paymentManager->getTransactionsId($order->transaction_id);
                if (isset($transaction['gatewayReferenceId'])) {
                    $order->gateway_reference_id = $transaction['gatewayReferenceId'];
                    $order->save();
                }
            }
        }
    }

    public function createCashType($order_id, $cashType)
    {
        CashType::create([
            'order_id' => $order_id,
            'type' => $cashType
        ]);
    }

    public function createCommissionReceipt($order, $payouts)
    {
        foreach ($payouts as $payout) {
            CommissionReceipt::create([
                'order_id' => $order->id,
                'transaction_id' => $payout['transactionId'],
                'user_id' => $payout['payeeUserId'],
                'amount' => $payout['amount'],
            ]);
        }
    }
}
