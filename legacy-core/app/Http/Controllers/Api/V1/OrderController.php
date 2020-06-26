<?php namespace App\Http\Controllers\Api\V1;

use DB;
use Log;

use App\Models\Order;
use App\Events\OrderWasFulfilled;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderStatusUpdateRequest;
use App\Repositories\Eloquent\AuthRepository;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\InvoiceRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\CartRepository;
use App\Repositories\Eloquent\GeocodeRepository;
use App\Repositories\Eloquent\CancellationRepository;
use App\Repositories\Eloquent\CouponAppliedRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\UserSettingsRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\Inventory\InventoryService;
use App\Services\PayMan\PayManService;
use App\Services\Orders\OrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\CustomOrderPayRequest;
use App\Services\Commission\CommissionService;
use App\Services\Shippo\ShippingServiceExport;
use App\Services\Tax\TaxService;
use App\Models\User;

class OrderController extends Controller
{
    /* @var \App\Repositories\Eloquent\OrderRepository */
    protected $orderRepo;

    /* @var \App\Repositories\Eloquent\InvoiceRepository */
    protected $invoiceRepo;

    /* @var \App\Repositories\Eloquent\UserRepository */
    protected $userRepo;

    /* @var \App\Repositories\Eloquent\UserSettingsRepository */
    protected $userSettingsRepo;

    /* @var \App\Services\Inventory\InventoryService */
    protected $inventoryService;

    public function __construct(
        AuthRepository $authRepo,
        CartRepository $cartRepo,
        CancellationRepository $cancellationRepo,
        CommissionService $commissionService,
        CouponAppliedRepository $couponAppliedRepo,
        GeocodeRepository $geoRepo,
        InventoryService $inventoryService,
        InvoiceRepository $invoiceRepo,
        OrderRepository $orderRepo,
        OrderService $orderService,
        PayManService $payMan,
        ProductRepository $productRepo,
        ShippingServiceExport $shippingServiceExport,
        UserRepository $userRepo,
        UserSettingsRepository $userSettingsRepo
    ) {
        $this->cancellationRepo = $cancellationRepo;
        $this->couponAppliedRepo = $couponAppliedRepo;
        $this->orderRepo = $orderRepo;
        $this->invoiceRepo = $invoiceRepo;
        $this->userRepo = $userRepo;
        $this->cartRepo = $cartRepo;
        $this->authRepo = $authRepo;
        $this->geoRepo = $geoRepo;
        $this->productRepo = $productRepo;
        $this->userSettingsRepo = $userSettingsRepo;
        $this->inventoryService = $inventoryService;
        $this->paymentManager = $payMan;
        $this->orderService = $orderService;
        $this->middleware('apiRole:admin', ['only' => ['ordersPerDay']]);
        $this->messages['OrderNotFound'] = 'The order or orders you are requesting cannot be found.';
        $this->commissionService = $commissionService;
        $this->shippingServiceExport = $shippingServiceExport;
        $this->settings = app('globalSettings');
    }

    /**
     * returns an index of orders
     *
     * returns index of all orders to admin users or returns to
     * all other authorized users an index of orders that they
     * have purchased
     *
     * @param query string request
     * @return json
     */
    public function index()
    {
        ini_set('memory_limit', '2048M');
        if (! auth()->check()) {
            return response()->json([$this->messages['Unauthorized']], 401);
        }
        $request = request()->all();

        // error out if date range is to high
        $startDate = Carbon::parse($request['start_date']);
        $endDate = Carbon::parse($request['end_date']);
        if ($startDate->diffInDays($endDate) > 70) {
            return response()->json(
                'Cannot pull more than 70 days'
                . ' at once, please decrease the date range.',
                400
            );
        }

        if (auth()->user()->hasRole(['Rep'])) {
            $request['store_owner_user_id'] = auth()->id();
        } elseif (auth()->user()->hasRole(['Admin', 'Superadmin'])) {
            $request['store_owner_user_id'] = config('site.apex_user_id');
        }
        if (! isset($request['fulfilled'])) {
            $request['fulfilled'] = '';
        }
        if (! empty($request['fulfilled'])) {
            $request['fulfilled'] = strtolower($request['fulfilled']);
        }

        $timezone = $this->userSettingsRepo->getUserTimeZone($request['store_owner_user_id']);
        $orders = $this->orderRepo->buildOrderIndexQuery($request)->get();

        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        //Define how many items we want to be visible in each page
        $perPage = isset($request['per_page']) ? (int)$request['per_page'] : 5;

        // offset a problem determining starting point when on page 1
        $startingPoint = 0;
        if ($currentPage > 1) {
            $startingPoint = ($currentPage  - 1) * $perPage;
        }

        //Slice the collection to get the items to display in current page
        $currentPageSearchResults = $orders->slice($startingPoint, $perPage)->all();
        $markers = $this->geoRepo->markerizeOrders($orders);
        //Create our paginator and pass it to the view
        $paginatedSearchResults = new LengthAwarePaginator($currentPageSearchResults, count($orders), $perPage);

        return response()->json([
            'orders' => $paginatedSearchResults,
            'markers' => $markers
        ], 200);
    }

    /**
     * returns an index of orders
     *
     * returns index of all orders to admin users or returns to
     * all other authorized users an index of orders that they
     * have purchased
     *
     * @param query string request
     * @return json
     */
    public function allOrdersAndInvoices()
    {
        $request = request()->all();
        $request['store_owner_user_id'] = $this->authRepo->getOwnerId();
        // make sure valid end and start dates, return no results if incorrect
        if (! (bool) strtotime($request['start_date']) ||
            ! (bool) strtotime($request['end_date'])) {
            $orders = new LengthAwarePaginator([], 0, 1);
            // create blank response
            return response()->json([
                    'orders' => $orders,
                    'markers' => []
                ], 200);
        }
        $allOrders = $this->orderRepo->buildOrderIndexQuery($request)->get();

        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        //Define how many items we want to be visible in each page
        $perPage = isset($request['per_page']) ? (int)$request['per_page'] : 5;

        // offset a problem determining starting point when on page 1
        $startingPoint = 0;
        if ($currentPage > 1) {
            $startingPoint = ($currentPage - 1) * $perPage;
        }

        //Slice the collection to get the items to display in current page
        $currentPageSearchResults = $allOrders->slice($startingPoint, $perPage)->all();
        // Grab map markers
        $markers = $this->geoRepo->markerizeOrders($allOrders);
        //Create our paginator and pass it to the view
        $paginatedSearchResults = new LengthAwarePaginator($currentPageSearchResults, count($allOrders), $perPage);

        return response()->json([
            'orders' => $paginatedSearchResults,
            'markers' => $markers
        ], 200);
    }

    /**
     * retrieve a single order
     *
     * this method returns a single order based on who is authorized
     * to view it
     *
     * @param int receiptId
     * @return json
     */
    public function show($receiptId)
    {
        if (! auth()->check()) {
            return $this->createResponse(true, 401, $this->messages['Unauthorized'], null);
        }

        if (auth()->user()->hasRole(['Admin', 'Superadmin'])) {
            $order = $this->orderRepo->findByReceiptId(
                $receiptId,
                [
                    'lines.item.product',
                    'lines.owner',
                    'customer',
                    'billingAddress',
                    'shippingAddress',
                    'bundles',
                    'storeOwner',
                    'coupons',
                    'tracking'
                ]
            );
        } else {
            $order = $this->orderRepo->findOrderByUser(
                auth()->user()->id,
                $receiptId,
                [
                    'lines.item.product',
                    // 'lines.returns.status', // this is breaking the orders page
                    'customer',
                    'billingAddress',
                    'shippingAddress',
                    'bundles',
                    'storeOwner',
                    'tracking'
                ]
            );
        }

        if (! $order) {
            return $this->createResponse(true, 500, $this->messages['OrderNotFound'], null);
        }

        if (auth()->user()->hasRole(['Admin', 'Superadmin'])) {
            $this->orderService->fulfilledByCorpFilter($order);
        }

        return response()->json($order, 200);
    }

    /**
     * returns a list of order types in json
     *
     * @param none
     * @return Json
     */
    public function orderTypes()
    {
        return response()->json($this->orderRepo->orderTypes(), 200);
    }

    public function showOrderTotalsByDate()
    {
        $date = Carbon::now();
        $endDate = $date->now()->toDateString();
        $startDate = $date->subMonth(3)->toDateString();
        $orders = $this->orderRepo->getOrderTotalsByDate($startDate, $endDate);
        return response()->json($orders);
    }

    public function ordersByRep($id = null)
    {
        $request = request()->all();
        if (($id !== null and (! auth()->user()->hasRole(['Superadmin', 'Admin']))) or $id === null) {
            $id = auth()->id();
        }
        //Define how many items we want to be visible in each page
        $perPage = isset($request['per_page']) ? (int)$request['per_page'] : 5;

        $timezone = $this->userSettingsRepo->getUserTimeZone($id);
        $startDate = Carbon::now($timezone)->subDays(30)->startOfDay()->setTimezone('UTC')->toDateTimeString();
        $endDate = Carbon::now($timezone)->endOfDay()->setTimezone('UTC')->toDateTimeString();
        $request = [
            'store_owner_user_id' => config('site.apex_user_id'),
            'customer_id'         => $id,
            'column'              => isset($request['column']) ? $request['column'] : 'created_at',
            'order'               => isset($request['order']) ? $request['order'] : 'ASC',
            'start_date'          => isset($request['start_date']) ? $request['start_date']: $startDate,
            'end_date'            => isset($request['end_date']) ? $request['end_date']: $endDate,
            'search_term'         => isset($request['search_term']) ? $request['search_term'] : '',
            'fulfilled'           => isset($request['fulfilled']) ? $request['fulfilled'] : '',
            'per_page'            => $perPage,
        ];
        $orders = $this->orderRepo->buildOrderIndexQuery($request)->get();
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        // offset a problem determining starting point when on page 1
        $startingPoint = 0;
        if ($currentPage > 1) {
            $startingPoint = ($currentPage - 1) * $perPage;
        }

        //Slice the collection to get the items to display in current page
        $currentPageSearchResults = $orders->slice($startingPoint, $perPage)->all();

        //Create our paginator and pass it to the view
        $paginatedSearchResults = new LengthAwarePaginator($currentPageSearchResults, count($orders), $perPage);

        return $paginatedSearchResults;
    }

    /**
     * bulk status update
     *
     * @param none
     * @return Json
     */
    public function updateStatus(OrderStatusUpdateRequest $orderStatusRequest)
    {
        $request = $orderStatusRequest->all();
        $orders = $this->orderRepo->getOrdersByReceiptId($request['orders'], ['customer']);

        // If we have a cancellation request
        if ($request['status'] == 'cancelled') {
            $cancelStatus = $this->cancellationRepo->addOrdersToCancellationQueue($orders);
        }

        $orderIdsToUpdate = [];
        foreach ($orders as $key => $order) {
            if ($request['status'] == 'fulfilled') {
                event(new OrderWasFulfilled($order));
            }
            $orderIdsToUpdate[] = $order->id;
        }
        Order::whereIn('id', $orderIdsToUpdate)
            ->update(['status' => $request['status']]);

        return response()->json($orders, 200);
    }

    public function create($data, $starterKit = false)
    {
        // Currently only used for starter kits, and will eventually be removed
        // Use the new checkout if the setting is on.
        $payouts = null;

        $storeOwner = $this->userRepo->find(config('site.apex_user_id'));
        $ownerId = $storeOwner->id;

        $cart = session()->get('cart');
        if (empty($cart)) {
            return response()->json(['Cart Not Found'], 422);
        }
        if (count($cart->lines) === 0 and count($cart->bundles) === 0) {
            return response()->json('The cart is empty. There is nothing to purchase...', 422);
        }

        //check availability of cartlines so we don't over sell product (it handles whose store it is)
        $inventoryTransfer = $this->inventoryService->reserveInventory($cart);
        if (!empty($inventoryTransfer->errors)) {
            return response()->json(['message' => 'Inventory not available.'], 422);
        }

        // verify we meet the correct min/max settings, error out if we dont
        foreach ($cart->lines as $line) {
            if ($line->item->product->min || $line->item->product->max) {
                $minMax = $this->productRepo->checkMinMax($line->item->id, $line->quantity);
                if ($minMax !== true) {
                    return response()->json(['description' => $minMax['error']['description']], 422);
                }
            }
        }

        // process order and inventory updates
        try {
            if (!empty($cart->coupons) && count($cart->coupons) > 0) {
                $couponOwnerId = config('site.apex_user_id');
                if (session()->has('store_owner') and session('store_owner.seller_type_id') === 2) {
                    $couponOwnerId = session()->get('store_owner.id');
                }
                $coupon = $this->couponAppliedRepo
                            ->checkAvailability($cart->coupons[0]->code, $couponOwnerId, $data['cart_type']);
                if (isset($coupon['error'])) {
                    return response()->json([$coupon['error']], 422);
                }
            }
            // if successful
            if (!isset($cart->user_id) || $cart->user_id == 0) {
                $customer = $this->userRepo->createCustomer($data['user']);
                $cart->user_id = $customer->id;
                $cart->save();
            }

            if ($ownerId === config('site.apex_user_id')) {
                $payouts = $this->orderService->fulfilledByCorporatePayouts($cart->toArray(), $ownerId);
            }

            // subtract inventory, run card, if card fails add inventory back
            $cart->lines->load('item.product');
            $seller = $storeOwner;
            // If the seller is an affiliate and a rep.
            if ($seller->seller_type_id === 1 && $seller->role_id === 5) {
                $seller = new User(['id' => config('site.apex_user_id'), 'role_id' => 8]);
            }
            if ($cart->total_price > 0) {
                $transaction = $this->paymentManager->makePayment(
                    'sale',
                    $customer->id,
                    1,
                    'company',
                    $data['payment'],
                    $cart->total_price,
                    $cart->total_tax,
                    $cart->total_shipping,
                    'Starter Kit Purchase',
                    $data['addresses']['billing']
                );

                if ($transaction['resultCode'] != 1) {
                    $this->inventoryService->cancelReservation($inventoryTransfer->reservation_group_id);
                    return response()->json(['payment' => [$transaction['result']]], 422);
                }
            } else {
                $transaction = null;
            }

            $orderTypeId = null;
            if ($starterKit) {
                // This needs to be defined beforehand or else we get a race condition when sending to the commission engine.
                $orderTypeId = 1; // Corporate to Rep
            }
            $order = $this->orderService->createOrder($ownerId, $cart, $data, $transaction, $orderTypeId);
            $order->load('customer', 'lines.item.product', 'sponsor', 'coupons');

            // Associates the customer with the store owner
            // to show that the customer has previously bought from them.
            $this->userRepo->attachCustomer(
                $order->storeOwner()->first(),
                $order->customer()->first()
            );

            if ($order) {
                $success = true;
                $inventoryUserId = null;
                $inventoryUserPid = null;
                if (!app('globalSettings')->getGlobal('inventory_confirmation', 'show')) {
                    if ($starterKit == true) {
                        $inventoryUserId = $cart->user->id;
                        $inventoryUserPid = $cart->user->pid;
                    } elseif (auth()->check() && auth()->user()->hasRole(['Rep'])) {
                        $inventoryUserId = auth()->user()->id;
                        $inventoryUserPid = auth()->user()->pid;
                    }
                } else {
                    if ($starterKit == true) {
                        $order->inventory_received_at = null;
                        $order->save();
                        $this->inventoryService->assureInventoryCreated($order); // Make sure rep gets empty inventory records
                    }
                }

                //manage inventory
                $this->inventoryService->transferReservation($inventoryTransfer->reservation_group_id, $inventoryUserId, $inventoryUserPid);
                $order->load('lines');

                $this->shippingServiceExport->queOrder($order);

                $orders[] = $order;
            }
            $this->cartRepo->delete($cart->uid);
            session()->forget('cart');
            session()->put('recent-orders', $orders);
            if (isset($success)) {
                // return success
                return response()->json($orders, 200);
            }
            // handle a checkout error, include a refund because card was charged
            $this->handleCheckoutError(
                $order,
                $data,
                $starterKit,
                $ownerId,
                $cart,
                $refund = true
            );
            return response()->json(['There was an error creating the order.'], 500);
        } catch (Exception $e) {
            // check if we need to submit a refund
            if (isset($transaction)) {
                $refund = true;
            } else {
                $refund = false;
            }
            // handle a checkout error
            $this->handleCheckoutError(
                $order,
                $data,
                $starterKit,
                $ownerId,
                $cart,
                $refund
            );
            return response()->json(['There was an error creating the order.'], 500);
        }
    }

    // handle errors for the order checkout process
    public function handleCheckoutError(
        $order,
        $data,
        $starterKit,
        $ownerId,
        $cart,
        $refund
    ) {
        // error, lets try to refund payment, log an error and return an error
        $error = 'Error when attempting to create a new order with order request: '
                    . json_encode($data)
                    . '  Starter kit: '
                    . $starterKit
                    . ' For owner ID: '
                    . $ownerId;
        if ($refund == true) {
            $refundResponse = $this->paymentManager->refundOrder($order);
            if (isset($refundResponse['error'])) {
                $error .= ' Payment was processed successfully and refund failed becase of'
                        . $refundResponse['error'];
            }
        }
        logger()->error($error);
    }

    // function to validate customer information
    public function validateCustomerInfo(Request $request)
    {
        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'shipping_address' => 'required',
            'shipping_address.address_1' => 'required',
            'shipping_address.zip' => 'required',
            'shipping_address.city' => 'required',
            'shipping_address.state' => 'required',
            'billing_address.address_1' => 'required',
            'billing_address.zip' => 'required',
            'billing_address.city' => 'required',
            'billing_address.state' => 'required',
        ];
        $this->validate($request, $rules);
        return "Valid customer info";
    }

    public function transferInventory(Request $request, $receiptId)
    {
        $order = $this->orderRepo->findByReceiptId($receiptId, ['lines', 'customer']);
        if (is_null($order->inventory_received_at)) {
            $this->inventoryService->addToRepInventory($order->lines, $order->customer);
            $order->inventory_received_at = Carbon::now()->toDateTimeString();
            $order->save();
        } else {
            return response()->json(['Could not transfer inventory'], 422);
        }
        return response()->json($order->inventory_received_at, 200);
    }
}
