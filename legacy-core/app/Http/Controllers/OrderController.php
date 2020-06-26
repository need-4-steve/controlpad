<?php namespace App\Http\Controllers;

use Auth;
use Cache;
use Validator;
use App\Events\OrderWasFulfilled;
use App\Models\Address;
use App\Models\Party;
use App\Models\Phone;
use App\Models\State;
use App\Models\User;
use App\Models\Order;
use App\Models\Invoice;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\InvoiceRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\CartRepository;
use App\Repositories\Eloquent\CouponAppliedRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Services\Inventory\InventoryService;
use App\Services\Store\RepStore;
use App\Services\Store\Store;
use App\Services\PayMan\PayManService;
use App\Services\Orders\OrderService;
use App\Services\Cart\WholesaleCartService;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

use Carbon\Carbon;

class OrderController extends Controller
{
    /* @var OrderRepository */
    protected $orderRepo;

    protected $cartRepo;

    protected $orderService;

    /* @var OrderRepository */
    protected $userRepo;

    protected $couponAppliedRepo;

    /**
     * OrderController constructor.
     *
     * @param OrderRepository $order
     */
    public function __construct(
        OrderRepository $orderRepo,
        InvoiceRepository $invoiceRepo,
        OrderService $orderService,
        UserRepository $userRepo,
        CartRepository $cartRepo,
        CouponAppliedRepository $couponAppliedRepo,
        PayManService $payMan,
        InventoryService $inventoryService,
        ProductRepository $productRepository,
        WholesaleCartService $cartService
    ) {
        $this->orderRepo = $orderRepo;
        $this->invoiceRepo = $invoiceRepo;
        $this->orderService = $orderService;
        $this->userRepo = $userRepo;
        $this->cartRepo = $cartRepo;
        $this->couponAppliedRepo = $couponAppliedRepo;
        $this->paymentManager = $payMan;
        $this->inventoryService = $inventoryService;
        $this->productRepository = $productRepository;
        $this->settingsService = app('globalSettings');
        $this->cartService = $cartService;

        $this->middleware('isAdmin', [
            'only' => [
                'edit',
                'update',
                'destroy',
                'delete',
                'getFulfillmentForm',
                'processFulfillment'
            ]
        ]);
    }

    public function index()
    {
        $admin = false;
        if (auth()->user()->hasRole(['Superadmin', 'Admin'])) {
            $admin = true;
        }
        return view('order.index', compact('admin'));
    }

    public function repIndex()
    {
        return view('order.rep-index');
    }

    public function show($receipt_id)
    {
        $type = 'order';
        $relationships = ['customer', 'sponsor', 'lines', 'addresses', 'bundles', 'storeOwner'];
        $order = $this->orderRepo->findByReceiptId($receipt_id);

        if ($order === null) {
            return redirect('orders')->with('message', 'No order found with receipt ID ' . $receipt_id);
        }
        $hideHold = true;
        if (auth()->user()->hasRole(['Admin', 'Superadmin']) ||
            auth()->id() === $order->store_owner_user_id &&
            $order->type_id !== 9
        ) {
            $hideHold = false;
        }

        if (auth()->user()->hasRole(['Superadmin', 'Admin']) ||
            $order->customer_id === auth()->id() ||
            $order->store_owner_user_id === auth()->id()
        ) {
            return view('order.show', compact('order', 'type', 'hideHold'));
        }
        return redirect('orders')->with('message', 'You do not have authorization to view that order.');
    }

    public function showInvoice($token)
    {
        $type = 'invoice';
        $relationships = ['customer', 'sponsor', 'lines', 'addresses', 'bundles'];
        $order = $this->invoiceRepo->find($token, $relationships);

        if ($order === null) {
            return redirect('orders')->with('message', 'No invoice found with ID ' . $token);
        }
        if (auth()->user()->id === $order->customer_id) {
            $hideHold = true;
        } else {
            $hideHold = false;
        }

        if (auth()->user()->hasRole(['Superadmin', 'Admin']) ||
            $order->customer_id === auth()->id() ||
            $order->store_owner_user_id === auth()->id()) {
            $order->receipt_id = $order->token;
            return view('order.show', compact('order', 'type', 'hideHold'));
        }

        return back()->with('message', 'You do not have authorization to view that order.');
    }

    public function custom()
    {
        $settings = app('globalSettings');
        if (count(cache()->get('user-status')) > 0
        && cache()->get('user-status')[auth()->user()->status]['sell']
        && (
            auth()->user()->hasRole(['Superadmin', 'Admin']) ||
            (auth()->user()->hasSellerType(['Affiliate']) && $settings->getGlobal('affiliate_custom_order', 'show')) ||
            (auth()->user()->hasSellerType(['Reseller']) && $settings->getGlobal('reseller_custom_order', 'show')))
        ) {
            return view('order.custom_order');
        }
        return response()->json('Feature disabled.', 403);
    }

    /**
     *
     */
    public function create()
    {
        $ordersUrl = env('ORDERS_API_URL', 'https://orders.controlpadapi.com/api/v0');
        $layout = 'public-cart';
        $autoshipUrl = env('AUTOSHIP_API_URL', 'https://autoship.controlpadapi.com/api/v0');

        return view('order.create', compact('ordersUrl', 'autoshipUrl', 'layout'));
    }

    public function edit($id)
    {
        $order = $this->orderRepo->find($id);
        return view('order.edit', compact('order'));
    }

    public function update(Request $request, $id)
    {
        $order = $this->orderRepo->find($id);
        if (! $order) {
            return back()->with('message', 'Could not find that order');
        }

        $order->update($request->all());
        Cache::forget('allorders');
        return redirect()->route('orders.show', $id)->with('message', 'Order updated.');
    }

    public function getFulfillmentForm(Request $request, $orderId = null)
    {
        if (! is_numeric($orderId) and $request->has('order_id')) {
            $orderId = $request->input('order_id');
        }

        if (is_null($orderId)) {
            return view('order.choose_order');
        }

        $order = $this->orderRepo->find($orderId);
        return view('order.fulfill', compact('order'));
    }

    public function processFulfillment(Request $request, $orderId)
    {
        $order = $this->orderRepo->find($orderId);
        if (! $order) {
            return back()->with('message', 'That order cannot be found');
        }

        $order->tracking_number = $request->input('tracking_number');
        if ($order->ship_from_corporate === true) {
            $order->shipped_from_corporate_at = Carbon::now()->toDateTimeString();
        } elseif ($order->ship_from_rep === true) {
            $order->shipped_from_rep_at = Carbon::now()->toDateTimeString();
        }
        $order->save();
        $order->load('user', 'lines', 'sponsor');

        event(new OrderWasFulfilled($order));
        return redirect('fulfill')->with('message', 'Order updated. Shipping Tracking #'.$order->tracking_number);
    }

    public function destroy($id)
    {
        if ($this->orderRepo->delete($id)) {
            Cache::forget('allorders');
            return redirect()->route('orders.index')->with('message', 'Order deleted.');
        }
        return back()->with('message', 'Could not destroy that order');
    }

    public function delete(Request $request)
    {
        $ids = $request->input('ids');
        foreach ($ids as $id) {
            $this->orderRepo->delete($id);
        }
        Cache::forget('allorders');

        $orderUsage = 'Order';
        if (count($ids) > 1) {
            str_plural($orderUsage);
        }

        return redirect()->route('orders.index')->with('message', $orderUsage . ' deleted.');
    }

    public function partyOrderShow($id)
    {
        $party = Party::with('orders')->find($id);
        return view('order.orders_by_party', compact('party'));
    }

    public function productsOrderedByDate()
    {
        return view('reports.products_ordered_by_date');
    }

    public function getOrder($id)
    {
        $order = Order::with('lines')->find($id);
        return view('barcode.fulfill-order', compact('order'));
    }

    public function receipt()
    {
        $orders = session()->get('recent-orders');
        if (isset($orders[0]) && $orders[0]->customer->role_id == 5 && auth()->check()) {
            $type = 'order';
            $hideHold = true;
            $order = $orders[0];
            return view('order.show', compact('order', 'type', 'hideHold'));
        } elseif ($orders) {
            return view('order.receipt', compact('orders'));
        } else {
            return 'Your session has ended and we can not show your receipt';
        }
    }

    public function localReceipt()
    {
        $layout = 'public-cart';

        return view('order.local_receipt', compact('layout'));
    }

    public function eInvoice($token)
    {
        $invoice = Invoice::where('token', $token)->first();
        if ($invoice) {
            return view('order.e_invoice', compact('token'));
        } else {
            return view('order.not_found', compact('token'));
        }
    }

    public function settings()
    {
        return view('order.settings');
    }
}
