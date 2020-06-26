<?php

namespace App\Http\Controllers\V0;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Repositories\EloquentV0\CartRepository;
use App\Repositories\EloquentV0\CouponRepository;
use App\Services\InventoryServiceInterface;
use App\Services\PaymanServiceInterface;
use App\Services\SettingsServiceInterface;
use App\Services\ShippingServiceInterface;
use App\Services\TaxServiceInterface;
use App\Services\UserServiceInterface;
use App\Cart;
use App\Cartline;
use App\Checkout;
use App\CommissionReceipt;
use App\Invoice;
use App\InvoiceItem;
use App\Order;
use App\Orderline;
use App\Coupon;
use CPCommon\Pid\Pid;

class CheckoutController extends Controller
{

    private $cartRepo;
    private $couponRepo;
    private $inventoryService;
    private $paymanService;
    private $settingsService;
    private $shippingService;
    private $taxService;
    private $userService;

    private const ROLE_DESC = [
        3 => 'Customer',
        5 => 'Rep',
        7 => 'Corporate',
        8 => 'Corporate'
    ];

    public function __construct(
        InventoryServiceInterface $inventoryService,
        PaymanServiceInterface $paymanService,
        SettingsServiceInterface $settingsService,
        ShippingServiceInterface $shippingService,
        TaxServiceInterface $taxService,
        UserServiceInterface $userService
    ) {
        $this->cartRepo = new CartRepository;
        $this->inventoryService = $inventoryService;
        $this->paymanService = $paymanService;
        $this->settingsService = $settingsService;
        $this->shippingService = $shippingService;
        $this->taxService = $taxService;
        $this->userService = $userService;
    }

    public function create(Request $request)
    {
        $this->validate($request, Checkout::$createRules);
        $isAdmin = $request->user->hasRole(['Admin', 'Superadmin']);
        if (!$isAdmin) {
            abort(403, 'Admin only');
        }
        $lines = json_decode(json_encode($request->input('lines')));
        foreach ($lines as $key => $line) {
            // Keep track of the cartline that was used
            if (isset($line->pid)) {
                $line->cartline_pid = $line->pid;
                unset($line->pid);
            }
            $line->orderline_pid = Pid::create();
        }
        $checkout = new Checkout($request->only(
            [
                'billing_address',
                'shipping_address',
                'subtotal',
                'type',
                'buyer_pid',
                'seller_pid',
                'inventory_user_pid',
                'coupon_id',
                'discount',
                'shipping',
                'couponable',
                'tax_exempt'
            ]
        ));

        $settings = $this->getCreateSettings();

        $checkout->lines = $lines;

        $inventoryUser = $this->userService->getUserbyPid($checkout->inventory_user_pid);
        $couponable = (
            $inventoryUser->role_id === 5 ?
            $settings->reseller_coupons->show :
            $settings->corp_coupons->show
        );
        if ($couponable) {
            if (empty($checkout->discount)) {
                if (!empty($checkout->coupon_id)) {
                    // Check for customer coupon restriction
                    if ($checkout->coupon->customer_id != null &&
                        ($checkout->buyer_pid == null || $this->userService->getUserbyPid($checkout->buyer_pid)->id !== $checkout->coupon->customer_id)
                    ) {
                        abort(422, json_encode(['result_code' => 9, 'result' => 'Coupon customer and buyer must be the same', 'message' => 'Coupon customer and buyer must be the same']));
                    }
                    // If not custom discount, has a coupon, can coupon then set discount from coupon
                    $checkout->discount = $checkout->coupon->calculateDiscount($cart->calculateSubtotal());
                    // Coupon cannot be override by default, but can be allowed through the request
                    $checkout->couponable = (isset($checkout->couponable) && $checkout->couponable);
                } else {
                    // Coupon can be used by default unless request disables it
                    $checkout->couponable = (!isset($checkout->couponable) || $checkout->couponable);
                }
            } else {
                // Cannot override custom discount with coupons
                $checkout->couponable = false;
            }
        } else {
            $checkout->couponable = false;
            $checkout->coupon_id = null;
        }

        return $this->createCheckout($checkout, $isAdmin, $settings, true);
    }

    public function createFromCart(Request $request, $cartPid)
    {
        $this->validate($request, [
            'discount' => 'numeric|min:0',
            'shipping' => 'numeric|min:0',
            'billing_address' => 'filled',
            'billing_address.line_1' => 'required_with:billing_address',
            'billing_address.city' => 'required_with:billing_address',
            'billing_address.state' => 'required_with:billing_address',
            'billing_address.zip' => 'required_with:billing_address',
            'billing_address.email' => 'email',
            'shipping_address' => 'filled',
            'shipping_address.line_1' => 'required_with:shipping_address',
            'shipping_address.city' => 'required_with:shipping_address',
            'shipping_address.state' => 'required_with:shipping_address',
            'shipping_address.zip' => 'required_with:shipping_address'
        ]);

        $cart = $this->cartRepo->cartByPid($cartPid);
        if (!$cart) {
            abort(404, 'Cart not found');
        }
        $isAdmin = $request->user->hasRole(['Admin', 'Superadmin']);
        if (isset($cart['buyer_pid']) && !$isAdmin && $request->user->pid != $cart['buyer_pid']) {
            abort(403, 'Buyer or admin only');
        }
        if ($cart->isEmpty()) {
            abort(400, 'Cart empty');
        }
        // Delete existing checkouts for the cart
        Checkout::where('cart_pid', '=', $cartPid)->delete();

        $checkout = new Checkout($request->only(['billing_address', 'shipping_address']));
        // Assign cart info to the checkout data
        $checkout->cart_pid = $cartPid;
        $checkout->subtotal = $cart->calculateSubtotal();
        $checkout->type = $cart->type;
        $checkout->buyer_pid = $cart->buyer_pid;
        $checkout->seller_pid = $cart->seller_pid;
        $checkout->inventory_user_pid = $cart->inventory_user_pid;
        $isCustom = $checkout->isCustom();
        $checkout->discount = ($isCustom ? $request->input('discount', 0.00) : 0.00);
        // Shipping must be null to allow a difference between custom 0 and not set
        $checkout->shipping = ($isCustom ? $request->input('shipping', null) : null);
        if ($checkout->type === 'custom-corp') {
            $checkout->tax_exempt = filter_var($request->input('tax_exempt', false), FILTER_VALIDATE_BOOLEAN);
        } elseif ($checkout->type === 'rep-transfer') {
            // Tax exempt when business to business
            $checkout->tax_exempt = true;
        } else {
            $checkout->tax_exempt = false;
        }

        $settings = $this->getCreateSettings();

        $inventoryUser = $this->userService->getUserbyPid($checkout->inventory_user_pid);
        $couponable = (
            $inventoryUser->role_id === 5 ?
            $settings->reseller_coupons->show :
            $settings->corp_coupons->show
        );
        if ($cart->type === 'custom-personal' || $cart->type === 'rep-transfer') {
            $couponable = false;
        }

        if ($couponable) {
            if (empty($checkout->discount)) {
                if (!empty($cart->coupon_id)) {
                    if ($cart->coupon->customer_id !== null) {
                        // If a coupon belongs to a specific customer, check buyer
                        if ($checkout->buyer_pid == null) {
                            abort(422, json_encode(['result_code' => 9, 'result' => 'Coupon customer and buyer must be the same', 'message' => 'Coupon customer and buyer must be the same']));
                        } else {
                            $buyer = $this->userService->getUserbyPid($checkout->buyer_pid);
                            if ($buyer->id !== $cart->coupon->customer_id) {
                                abort(422, json_encode(['result_code' => 9, 'result' => 'Coupon customer and buyer must be the same', 'message' => 'Coupon customer and buyer must be the same']));
                            }
                        }
                    }
                    if (!$cart->coupon->isExpired()) {
                        // If not custom discount, and has a coupon, set discount from coupon
                        $checkout->discount = $cart->coupon->calculateDiscount($cart->calculateSubtotal());
                        $checkout->coupon_id = $cart->coupon_id;
                        $checkout->couponable = false;
                    } else {
                        $cart->update(['coupon_id' => null]);
                        $checkout->couponable = true;
                    }
                } else {
                    $checkout->couponable = true;
                }
            } else {
                $checkout->couponable = false;
            }
        } else {
            $checkout->couponable = false;
            $checkout->coupon_id = null;
        }

        $lines = $cart->lines;
        $reserve = $request->input('reserve_inv', false);

        foreach ($lines as $key => $line) {
            // Keep track of the cartline that was used
            if (isset($line->pid)) {
                $line->cartline_pid = $line->pid;
                $line->price = round($line->price, 2);
            }
            $line->orderline_pid = Pid::create();
        }
        $checkout->lines = $lines;


        return $this->createCheckout($checkout, $isAdmin, $settings, null, $reserve);
    }

    public function createFromInvoice(Request $request, $token)
    {
        $this->validate($request, [
            'billing_address' => 'filled',
            'billing_address.line_1' => 'required_with:billing_address',
            'billing_address.city' => 'required_with:billing_address',
            'billing_address.state' => 'required_with:billing_address',
            'billing_address.zip' => 'required_with:billing_address',
            'billing_address.email' => 'email',
            'shipping_address' => 'filled',
            'shipping_address.line_1' => 'required_with:shipping_address',
            'shipping_address.city' => 'required_with:shipping_address',
            'shipping_address.state' => 'required_with:shipping_address',
            'shipping_address.zip' => 'required_with:shipping_address',
        ]);

        $invoice = Invoice::where('token', '=', $token)->first();
        if ($invoice === null) {
            abort(404, 'Invoice missing');
        }
        if ($invoice->order_id !== null) {
            abort(422, json_encode(['message' => 'Invoice paid already']));
        }
        if ($invoice->isExpired() || $invoice->deleted_at !== null) {
            abort(422, json_encode(['message' => 'Invoice expired']));
        }
        $settings = $this->settingsService->getSettings(['tax_calculation', 'company_pid']);

        $seller = $this->userService->getUserById($invoice->store_owner_user_id);
        if ($invoice->type_id === 9) {
            $inventoryUserPid = $settings->company_pid->value;
        } else {
            $inventoryUserPid = $seller->pid;
        }
        $buyer = $this->userService->getUserById($invoice->customer_id, true);

        $subtotal = 0.00; // Temporary workaround to fix old jank subtotal
        $lines = InvoiceItem::select('item_id', 'bundle_id', 'quantity', 'price')->where('invoice_id', '=', $invoice->id)->get();
        $itemIds = []; // Gather item ids to pull inventory/item data
        $bundleIds = [];
        foreach ($lines as $key => $line) {
            $line->orderline_pid = Pid::create();
            $line->inventory_owner_pid = $inventoryUserPid;
            if (isset($line->item_id)) {
                $itemIds[] = $line->item_id;
            } elseif (isset($line->bundle_id)) {
                $bundleIds[] = $line->bundle_id;
            }
            $subtotal += ($line->price * $line->quantity);
        }
        // Find and map items
        $itemMap = [];
        if (sizeof($itemIds) > 0) {
            $items = $this->inventoryService->getInventories($itemIds, $inventoryUserPid)->data;
            foreach ($items as $key => $item) {
                $itemMap[$item->id] = $item;
            }
        } else {
            abort(400, 'Invoice empty');
        }

        $bundleMap = [];
        if (sizeof($bundleIds) > 0) {
            $bundles = $this->inventoryService->getBundles($bundleIds, $inventoryUserPid)->data;
            foreach ($bundles as $key => $bundle) {
                $bundleMap[$bundle->id] = $bundle;
            }
        }

        foreach ($lines as $key => $line) {
            if ($line->item_id != null) {
                if (array_key_exists($line->item_id, $itemMap)) {
                    $item = $itemMap[$line->item_id];
                    $line->tax_class = $item->variant->product->tax_class;
                    $line->bundle_name = null;
                    // Single item in the items array
                    if (isset($item->variant->images[0]->url)) {
                        $itemImageUrl = $item->variant->images[0]->url;
                    } elseif (isset($item->variant->product->images[0]->url)) {
                        $itemImageUrl = $item->variant->product->images[0]->url;
                    } else {
                        $itemImageUrl = null;
                    }
                    $line->items = [[
                        'id' => $item->id,
                        'inventory_id' => $item->inventory_id,
                        'product_name' => $item->variant->product->name,
                        'variant_name' => $item->variant->name,
                        'option_label' => $item->variant->option_label,
                        'option' => $item->option,
                        'sku' => $item->sku,
                        'weight' => $item->weight,
                        'premium_shipping_cost' => $item->premium_shipping_cost,
                        'img_url' => $itemImageUrl
                        ]];
                } else {
                    app('log')->error('Failed to find item for invoice checkout.', ['invoice' => $invoice, 'line' => $line]);
                    abort(500);
                }
            } else {
                if (array_key_exists($line->bundle_id, $bundleMap)) {
                    $bundle = $bundleMap[$line->bundle_id];
                    $line->tax_class = $bundle->tax_class;
                    $line->bundle_name = $bundle->name;
                    $filterItems = [];
                    foreach ($bundle->items as $key => $item) {
                        if (isset($item->variant->images[0]->url)) {
                            $itemImageUrl = $item->variant->images[0]->url;
                        } elseif (isset($item->variant->product->images[0]->url)) {
                            $itemImageUrl = $item->variant->product->images[0]->url;
                        } else {
                            $itemImageUrl = null;
                        }
                        $filterItems[] = [
                            'id' => $item->id,
                            'inventory_id' => $item->inventory_id,
                            'product_name' => $item->variant->product->name,
                            'variant_name' => $item->variant->name,
                            'option_label' => $item->variant->option_label,
                            'option' => $item->option,
                            'sku' => $item->sku,
                            'weight' => $item->weight,
                            'quantity' => $item->quantity,
                            'premium_shipping_cost' => $item->premium_shipping_cost,
                            'img_url' => $itemImageUrl,
                            'variant_label' => $item->variant->product->variant_label
                        ];
                    }
                    $line->items = $filterItems;
                } else {
                    app('log')->error('Failed to add bundle to cart.', ['cart_pid' => $cart->pid, 'bundle_id' => $cartLine->bundle_id]);
                    abort(500);
                }
            }
        }
        if (!empty($invoice->coupon_code)) {
            $couponRepo = new CouponRepository;
            $coupon = $couponRepo->couponByCode($invoice->coupon_code, $inventoryUserPid);
            $couponId = ($coupon ? $coupon->id : null);
        } else {
            $couponId = null;
        }

        $checkout = new Checkout($request->only(['billing_address', 'shipping_address']));
        $checkout->pid = Pid::create();
        $checkout->discount = $invoice->total_discount;
        $checkout->shipping = $invoice->total_shipping;
        $checkout->shipping_rate_id = $invoice->shipping_rate_id;
        $checkout->subtotal = round($subtotal, 2);
        $checkout->coupon_id = $couponId;
        $checkout->couponable = $invoice->couponable;
        $checkout->type = $this->convertInvoiceOrderTypeToCheckoutType($invoice->type_id);
        $checkout->buyer_pid = $buyer->pid;
        $checkout->seller_pid = $seller->pid;
        $checkout->inventory_user_pid = $inventoryUserPid;
        $checkout->invoice_id = $invoice->id;
        $checkout->lines = $lines;
        $checkout->tax_exempt = ($invoice->type_id === 11); // Tax exempt when business to business

        if ($checkout->billing_address === null && !empty($buyer->billing_address->zip)) {
            $checkout->billing_address = $buyer->billing_address;
        }
        if ($checkout->shipping_address === null && !empty($buyer->shipping_address->zip)) {
            $checkout->shipping_address = $buyer->shipping_address;
        }
        $checkout->shipping_is_billing = false;

        $settings = $this->getCreateSettings();


        // Check tax settings
        if (!$checkout->tax_exempt && filter_var($settings->tax_calculation->show, FILTER_VALIDATE_BOOLEAN)
            && (!$checkout->isWholesale() || !filter_var($settings->tax_exempt_wholesale->show, FILTER_VALIDATE_BOOLEAN))) {
            // Calculate taxes if turned on unless wholesale is exempt
            $this->createTaxes($checkout);
        } else {
            $checkout->tax_invoice_pid = null;
            $checkout->tax = 0.00;
        }

        $checkout->total = round($checkout->subtotal - $checkout->discount + $checkout->shipping + $checkout->tax, 2);
        Checkout::where('invoice_id', $invoice->id)->delete(); // Don't allow multiple checkouts for the same invoice
        $checkout->save();
        $checkout->buyer = $buyer;
        return response()->json($checkout, 201);
    }

    private function createCheckout(Checkout $checkout, $isAdmin, $settings, $isCustom = null, $reserveInventory = false)
    {
        if ($isCustom === null) {
            $isCustom = $checkout->isCustom();
        }
        $checkout->pid = Pid::create();
        $checkout->shipping_is_billing = false;

        // TODO check if an affiliate is trying to purchase wholesale while setting is off
        if ($checkout->type === 'wholesale' && $settings->wholesale_cart_min->show) {
            $cartMinType = $settings->wholesale_cart_min->value;
            $cartMinAmount = $settings->wholesale_cart_min_amount->value;
            if ($cartMinType === 'dollar' && $cartMinAmount > $checkout->subtotal) {
                return response()->json(
                    [
                        'message' => 'Minimum order amount is $'. $cartMinAmount .
                        '. Please add more items to your cart.'
                    ],
                    400
                );
            } elseif ($cartMinType === 'quantity' && $cartMinAmount > $checkout->getItemsQuantity()) {
                return response()->json(
                    [
                        'message' => 'Minimum order quantity is '. $cartMinAmount
                        . ' items.  Please add more items to your cart.'
                    ],
                    400
                );
            }
        }

        if (isset($checkout->shipping) && $isCustom) {
            // Allow setting shipping for custom carts
            $checkout->shipping_rate_id = 0;
        } elseif ($checkout->type !== 'custom-personal') {
            // Shipping is calculated from discounted amount
            $shippingRate = $this->shippingService->findRate($checkout->inventory_user_pid, $checkout->type, $checkout->subtotal);
            if ($shippingRate == null) {
                abort(501, 'Shipping rates have not been set. Contact store owner to resolve this issue.');
            }
            $checkout->shipping_rate_id = $shippingRate->id;
            $checkout->shipping = $shippingRate->amount + $checkout->getPremiumShipping();
        } else {
            $checkout->shipping = 0.00;
            $checkout->shipping_rate_id = 0;
        }

        // Check tax settings
        if (!$checkout->tax_exempt && filter_var($settings->tax_calculation->show, FILTER_VALIDATE_BOOLEAN)
            && (!$checkout->isWholesale() || !filter_var($settings->tax_exempt_wholesale->show, FILTER_VALIDATE_BOOLEAN))) {
            // Calculate taxes if turned on unless wholesale is exempt
            $this->createTaxes($checkout);
        } else {
            $checkout->tax_invoice_pid = null;
            $checkout->tax = 0.00;
        }

        $checkout->total = round($checkout->subtotal - $checkout->discount + $checkout->shipping + $checkout->tax, 2);

        // Reserve inventory if requested
        if ($reserveInventory) {
            // Reserve or refresh inventory reservation for checkout. Do not partial reserve
            $inventoryTransfer = $this->inventoryService->reserveInventoryForCheckout($checkout, false, false);
            if (!empty($inventoryTransfer->errors)) {
                // Couldn't reserve inventory
                // Cancel any remainder
                if (!empty($inventoryTransfer->reservations)) {
                    $this->inventoryService->cancelReservation($inventoryTransfer->reservation_group_id);
                }
                abort(
                    422,
                    json_encode([
                        'reservationResponse' => $inventoryTransfer,
                        'result_code' => 4,
                        'result' => 'Inventory partially available.',
                        'message' => 'Some product unavailable.'
                    ])
                );
            }
        }

        foreach ($checkout->lines as $key => $line) {
            unset($line->pid);
        }
        $checkout->save();
        return response()->json($checkout, 201);
    }

    public function show(Request $request, $pid)
    {
        $isAdmin = $request->user->hasRole(['Admin', 'Superadmin']);
        $checkout = Checkout::where('pid', $pid)->first();
        if ($checkout === null) {
            abort(404, 'Checkout missing');
        }
        if (isset($checkout->buyer_pid) && !$isAdmin && $request->user->pid != $checkout->buyer_pid) {
            abort(403, 'Buyer or admin only');
        }

        return response()->json($checkout);
    }

    public function update(Request $request, $pid)
    {
        $this->validate($request, [
            'discount' => 'numeric|min:0',
            'shipping' => 'numeric|min:0',
            'billing_address.line_1' => 'required_with:billing_address',
            'billing_address.city' => 'required_with:billing_address',
            'billing_address.state' => 'required_with:billing_address',
            'billing_address.zip' => 'required_with:billing_address',
            'billing_address.email' => 'email',
            'shipping_address.line_1' => 'required_with:shipping_address',
            'shipping_address.city' => 'required_with:shipping_address',
            'shipping_address.state' => 'required_with:shipping_address',
            'shipping_address.zip' => 'required_with:shipping_address',
        ]);
        $data = $request->only(['billing_address', 'shipping_address', 'discount', 'shipping', 'coupon_code', 'self_pickup']);

        $isAdmin = $request->user->hasRole(['Admin', 'Superadmin']);
        $checkout = Checkout::where('pid', $pid)->first();
        $checkoutChange = false;
        if ($checkout == null) {
            abort(404, 'Checkout missing');
        }
        if (!$isAdmin && !isset($checkout->invoice_id) && isset($checkout->buyer_pid) && $request->user->pid != $checkout->buyer_pid) {
            abort(403, 'Buyer or admin only');
        }
        $settings = $this->settingsService->getSettings(['tax_calculation', 'tax_exempt_wholesale', 'self_pickup_wholesale', 'self_pickup_reseller']);

        if (isset($data['discount']) && $checkout->discount !== $data['discount'] && $checkout->isCustom()) {
            $checkoutChange = true;
            $checkout->discount = $data['discount'];
            $checkout->coupon_id = null;
            if ($checkout->discount > $checkout->subtotal) {
                $checkout->discount = $checkout->subtotal;
            }
        } elseif (!empty($data['coupon_code']) && $checkout->couponable) {
            $couponRepo = new CouponRepository;
            $coupon = $couponRepo->couponByCode($data['coupon_code'], $checkout->inventory_user_pid);
            if ($coupon == null) {
                return response()->json(['coupon_code' => ['No coupon found']], 422);
            }
            // Check for customer coupon restriction
            if ($coupon->customer_id != null &&
                ($checkout->buyer_pid == null || $this->userService->getUserbyPid($checkout->buyer_pid)->id !== $coupon->customer_id)
            ) {
                abort(422, json_encode(['result_code' => 9, 'result' => 'Coupon customer and buyer must be the same', 'message' => 'Coupon customer and buyer must be the same']));
            }
            switch ($checkout->type) {
                case 'custom-wholesale':
                case 'wholesale':
                    if ($coupon->type !== 'wholesale') {
                        return response()->json(['code' => ['Wholesale coupons only']], 422);
                    }
                    break;
                case 'retail':
                case 'affiliate':
                case 'custom-affiliate':
                case 'custom-corp':
                case 'custom-retail':
                    if ($coupon->type !== 'retail') {
                        return response()->json(['code' => ['Retail coupons only']], 422);
                    }
                    break;
                case 'custom-personal':
                default:
                    return response()->json(['code' => ['Cannot apply coupons to this order']], 422);
            }
            if ($coupon->isExpired()) {
                return response()->json(['coupon_code' => ['Coupon is expired.']], 422);
            }
            if ($coupon->uses >= $coupon->max_uses) {
                return response()->json(['coupon_code' => ['Coupon use limit exceeded']], 422);
            }
            if ($checkout->coupon_id !== $coupon->id) {
                $checkoutChange = true;
                $checkout->coupon_id = $coupon->id;
                $checkout->discount = $coupon->calculateDiscount($checkout->subtotal);
            }
        } elseif (array_key_exists('coupon_code', $data) && empty($data['coupon_code'])) {
            // Allow removing coupon code by sending empty or null code
            if ($checkout->coupon_id !== null) {
                $checkout->coupon_id = null;
                $checkout->discount = 0.00;
                $checkoutChange = true;
            }
        }
        if (isset($data['self_pickup']) && $checkout->self_pickup !== $data['self_pickup']) {
            $checkoutChange = true;
            $checkout->self_pickup = $data['self_pickup'];
        }
        if (isset($data['shipping']) && $checkout->shipping !== $data['shipping'] && $checkout->isCustom()) {
            $checkoutChange = true;
            $checkout->shipping = $data['shipping'];
        }
        if (isset($data['billing_address']) && $checkout->billing_address != $data['billing_address']) {
            $checkoutChange = true;
            $checkout->billing_address = $data['billing_address'];
        }
        if (isset($data['shipping_address']) && $checkout->shipping_address != $data['shipping_address']) {
            $checkoutChange = true;
            $checkout->shipping_address = $data['shipping_address'];
        }

        // If checkout has changed then recalculate taxes
        if ($checkoutChange) {
            $this->recalculateCheckout($checkout, $settings);
        }

        return response()->json($checkout, 200);
    }

    public function delete(Request $request, $pid)
    {
        $isAdmin = $request->user->hasRole(['Admin', 'Superadmin']);
        $checkout = Checkout::where('pid', $pid)->first();
        if ($checkout == null) {
            abort(404, 'Checkout missing');
        }
        if (!$isAdmin && !isset($checkout->invoice_id) && isset($checkout->buyer_pid) && $request->user->pid != $checkout->buyer_pid) {
            abort(403, 'Buyer or admin only');
        }
        if (isset($checkout->tax_invoice_pid)) {
            $this->taxService->deleteTaxInvoice($checkout->tax_invoice_pid);
        }
        if (isset($checkout->transfer_pid)) {
            $this->inventoryService->cancelReservation($checkout->transfer_pid);
        }
        $checkout->delete();
        return response(null, 202);
    }

    public function process(Request $request, $pid)
    {
        $isAdmin = $request->user->hasRole(['Admin', 'Superadmin']);
        // Get checkout
        $checkout = Checkout::where('pid', $pid)->first();
        if (!$checkout) {
            abort(400, 'No checkout found');
        }

        // Check permission/ownership
        if (!$isAdmin && !isset($checkout->invoice_id) && isset($checkout->buyer_pid) && $request->user->pid != $checkout->buyer_pid) {
            abort(403, 'Buyer or admin only');
        }
        if (empty($checkout->shipping_address)) {
            return response()->json([
                'checkout' => $checkout,
                'result_code' => 6,
                'result' => 'Shipping Address not set.',
                'message' => 'Shipping Address required.'
            ], 422);
        }
        $validations = [
            'payment' => 'filled|required',
            'payment.type' => 'required|string|in:cash,card,card-token,e-wallet',
            'payment.amount' => 'required|numeric|min:0',
            'payment.card' => 'filled|required_if:payment.type,card',
            'payment.card.name' => 'required_with:payment.card|string',
            'payment.card.number' => 'required_with:payment.card',
            'payment.card.month' => 'required_with:payment.card|integer',
            'payment.card.year' => 'required_with:payment.card|integer',
            'payment.card.code' => 'string',
            'payment.card_magstripe' => 'required_if:payment.type,card-swipe',
            'payment.card_magstripe_enc' => 'required_if:payment.type,card-swipe-enc',
            'payment.card_token' => 'required_if:payment.type,card-token',
            'payment.payment_id' => 'required_if:payment.type,payment-id',
        ];
        if (!isset($checkout->buyer_pid) || $checkout->requiresBuyer()) {
            $validations['buyer'] = 'filled|required';
            $validations['buyer.first_name'] = 'required';
            $validations['buyer.last_name'] = 'required';
            $validations['buyer.email'] = 'required|email';
        }
        // For now we only accept one payment
        $this->validate($request, $validations);

        $source = $request->input('source', 'unknown');
        $payment = $request->input('payment');
        $reserveInventory = $request->input('reserve_inv', true);
        $partialReserve = $request->input('partial_reserve', false);
        // Make sure amount matches
        if ($payment['amount'] != $checkout->total) {
            abort(400, "Payment amount does not equal checkout total");
        }
        $settings = $this->settingsService->getSettings(
            ['simple_commissions', 'inventory_confirmation', 'tax_calculation', 'use_built_in_store',
                'tax_exempt_wholesale', 'wholesale_ewallet', 'wholesale_card_token',
                'payman_affiliate_team', 'use_commission_engine', 'self_pickup_wholesale',
                'self_pickup_reseller',
            ]
        );
        // Validate payment
        $orderPaymentType = null;
        switch ($payment['type']) {
            case 'cash':
                // cash is admin, owner or zero total only
                if (!$isAdmin && $request->user->pid != $checkout->inventory_user_pid && $checkout->total > 0) {
                    abort(403, 'Cash payment is authorized by inventory owner or zero total amount only');
                }
                $orderPaymentType = 'cash';
                break;
            case 'card':
            case 'credit-card':
                $this->validateCard($payment['card']);
                $orderPaymentType = 'credit-card';
                break;
            case 'card-token':
                if ($checkout->type !== 'wholesale') {
                    abort(400, "Card token payments are for wholesale only");
                }
                if (!$settings->wholesale_card_token->show) {
                    abort(400, "Card token payments are disabled for wholesale");
                }
                $orderPaymentType = 'card-token';
                break;
            case 'e-wallet':
                if ($checkout->type !== 'wholesale') {
                    abort(400, "E-wallet payments are for wholesale only");
                }
                if (!$settings->wholesale_ewallet->show) {
                    abort(403, "E-wallet payments are disabled for wholesale");
                }
                $orderPaymentType = 'e-wallet';
                break;
            default:
                abort(400, "Invalid payment type: " . $payment['type']);
        }
        if ($checkout->invoice_id !== null) {
            if ($checkout->invoice === null || $checkout->invoice->isExpired()) {
                abort(422, json_encode(['result_code' => 11, 'result' => 'Invoice expired.', 'message' => 'Invoice has expired.']));
            }
            if ($checkout->invoice->order_id !== null) {
                abort(422, json_encode(['result_code' => 12, 'result' => 'Invoice already paid.', 'message' => 'Invoice has been paid.']));
            }
        }
        // Find seller
        $seller = $this->userService->getUserByPid($checkout->seller_pid);

        $buyerInput = $request->input('buyer');

        // Get or Create buyer so we have an id
        if (empty($buyerInput)) {
            $buyer = $this->userService->attachCustomer($seller->id, $checkout->buyer_pid);
        } else {
            if ($checkout->type === 'rep-transfer') {
                $customer = $this->userService->getUserByPid($buyerInput['pid']);
                if ($customer == null || $customer->role_id !== 5) {
                    abort(400, json_encode(['message' => 'Customer must be a rep for ' . $checkout->type]));
                }
                $buyer = (object)$buyerInput;
                $buyer->id = $customer->id;
                $buyer->role_id = $customer->role_id;
            } elseif ($checkout->requiresBuyer() || !isset($checkout->buyer_pid)) {
                // User service will find or create for us
                $buyerInput['shipping_address'] = $checkout->shipping_address;
                $buyerInput['billing_address'] = $checkout->billing_address;
                $customer = $this->userService->createCustomer($seller->id, $buyerInput);
                $buyer = $customer;
            } else {
                $buyer = (object)$buyerInput;
                if ($checkout->invoice_id != null && !$checkout->isWholesale()) {
                    // For invoices lets update the customer so address auto fill works
                    $customer = $this->userService->createCustomer($seller->id, $buyerInput);
                } else {
                    $customer = $this->userService->attachCustomer($seller->id, $checkout->buyer_pid);
                }
                $buyer->role_id = $customer->role_id;
                $buyer->id = $customer->id;
                $buyer->pid = $checkout->buyer_pid;
            }
        }

        // This should only be a temporary solution until the problem is figured out.
        if ($this->parseOrderType($checkout->type, $seller, $buyer) === 2 && $source === 'Web' && $settings->use_built_in_store->show === false) {
            app('log')->error('Unexpected Checkout Type', json_decode(json_encode([
                'request' => $request->all(),
                'user' => $request->user,
                'buyer' => $buyer,
                'seller' => $seller,
                'checkout' => $checkout,
                'fingerprint' => 'Unexpected Checkout Type',
            ]), true));
            abort(422, json_encode(['result_code' => 13, 'result' => 'Invalid Order Type with Source', 'message' => 'Source Web is currently not valid with order type Corp to Customer', 'checkout' => $checkout]));
        }

        if ($checkout->seller_pid === $checkout->inventory_user_pid) {
            $inventoryOwner = $seller;
        } else {
            $inventoryOwner = $this->userService->getUserByPid($checkout->inventory_user_pid);
        }
        $paymentDetails = $this->calculatePaymentDetails($checkout->type, $seller, $inventoryOwner, $buyer, $settings->payman_affiliate_team->value);
        if ($paymentDetails === null) {
            app('log')->error('Failed to get payment team.', ['checkout' => $checkout, 'seller' => $seller]);
            abort(500, 'Unexpected error');
        }

        // Pre check coupon
        if ($checkout->coupon_id !== null) {
            $couponRepo = new CouponRepository;
            if (!$couponRepo->isCouponAvailable($checkout->coupon_id)) {
                $checkout->coupon_id = null;
                $checkout->discount = 0.00;
                $this->recalculateCheckout($checkout, $settings);
                abort(422, json_encode(['result_code' => 8, 'result' => 'Coupon limit reached.', 'message' => 'No more uses left on coupon.', 'checkout' => $checkout]));
            }
        }

        // Don't reserve inventory for invoices
        if ($checkout->invoice_id === null) {
            // Reserve or refresh inventory reservation for checkout. Checks for existing reservation.
            $inventoryTransfer = $this->inventoryService->reserveInventoryForCheckout($checkout, $partialReserve);
            // If partial reserve, sync will update checkout and throw an error
            $this->inventorySync($checkout, $inventoryTransfer, $settings);
            $eventErrors = $this->checkEventItems($checkout);
            if (count($eventErrors) > 0) {
                $this->inventoryService->cancelReservation($inventoryTransfer->reservation_group_id);
                $checkout->transfer_pid = null;
                $checkout->save();
                abort(422, json_encode(['items' => $eventErrors]));
            }
        }


        $orderPid = Pid::create();

        // Check for simple simple_commissions
        $affiliatePayouts = [];
        if (in_array($checkout->type, ['affiliate', 'custom-affiliate']) && $settings->simple_commissions->show) {
            $affiliatePayouts[] = [
                'payeeUserId' => $seller->id,
                'amount' => (($checkout->subtotal - $checkout->discount) * (doubleval($settings->simple_commissions->value) / 100))
            ];
        }

        // Auth payment
        $transaction = $this->paymanService->authorizePayment(
            $paymentDetails['teamId'],
            $inventoryOwner,
            $buyer,
            $payment,
            $checkout->tax,
            $checkout->shipping,
            $checkout->discount,
            $orderPid,
            $paymentDetails['description'],
            $affiliatePayouts
        );
        if ($transaction->resultCode != 1) {
            // Failed to authorize/validate payment
            $this->releaseEventItems($checkout);
            abort(422, json_encode(['result_code' => 5, 'result' => 'Payment failed.', 'message' => $transaction->result, 'transaction' => $transaction]));
        }

        // Capture coupon use
        if ($checkout->coupon_id != null) {
            if (!$couponRepo->addUse($checkout->coupon_id)) {
                $this->paymanService->cancelTransaction($transaction->id, $transaction->amount);
                $this->releaseEventItems($checkout);
                $checkout->coupon_id = null;
                $checkout->discount = 0.00;
                $this->recalculateCheckout($checkout, $settings);
                abort(422, json_encode(['result_code' => 8, 'result' => 'Coupon limit reached.', 'message' => 'No more uses left on coupon.', 'checkout' => $checkout]));
            }
        }

        $dateTime = Carbon::now()->setTimezone('UTC')->toDateTimeString();
        $isCash = ($transaction->transactionType === 'cash-sale');
        $cashType = ($isCash && !empty($payment['cash_type']) ? $payment['cash_type'] : null);
        try {
            $order = new Order([
                'pid' => $orderPid,
                'confirmation_code' => random_int(1000123, 9999979586),
                'customer_id' => $buyer->id,
                'store_owner_user_id' => $seller->id, // Deprecating
                'buyer_pid' => $buyer->pid,
                'buyer_first_name' => $buyer->first_name,
                'buyer_last_name' => $buyer->last_name,
                'buyer_email' => $buyer->email,
                'seller_pid' => $seller->pid,
                'seller_name' => $seller->first_name . ' ' . $seller->last_name,
                'type_id' => $this->parseOrderType($checkout->type, $seller, $buyer),
                'transaction_id' => $transaction->id,
                'gateway_reference_id' => $transaction->gatewayReferenceId,
                'total_price' => $checkout->total,
                'subtotal_price' => $checkout->subtotal,
                'total_discount' => $checkout->discount,
                'total_tax' => $checkout->tax,
                'total_shipping' => $checkout->shipping,
                'tax_invoice_pid' => $checkout->tax_invoice_pid,
                'shipping_rate_id' => ($checkout->shipping_rate_id ? $checkout->shipping_rate_id : 0),
                'coupon_id' => $checkout->coupon_id,
                'paid_at' => null,
                'cash' => $isCash,
                'payment_type' => $orderPaymentType,
                'cash_type' => $cashType,
                'status' => 'hold',
                'source' => $source,
                'deleted_at' => null,
                'comm_engine_status_id' => 0, // This will prevent commissions from triggering until order is finished
                'tax_not_charged' => $checkout->tax_exempt,
                'billing_address' => $checkout->billing_address,
                'shipping_address' => $checkout->shipping_address,
                'created_at' => $dateTime,
                'updated_at' => $dateTime
            ]);
            $order->save();
            $receiptId = 'O'.strtoupper(str_random(5)).'-'.$order->id;
            $order->receipt_id = $receiptId;
            $order->confirmation_code = $receiptId;
            $order->save();

            $lines = [];
            foreach ($checkout->lines as $key => $line) {
                $lines[] = [
                    'order_id' => $order->id,
                    'item_id' => $line->item_id,
                    'bundle_id' => $line->bundle_id,
                    'bundle_name' => $line->bundle_name,
                    'type' => ($line->bundle_id === null ? 'Product' : 'Bundle'),
                    'name' => (isset($line->bundle_name) ? $line->bundle_name : (isset($line->items[0]->product_name) ? $line->items[0]->product_name : '')),
                    'price' => $line->price,
                    'quantity' => $line->quantity,
                    'manufacturer_sku' => ($line->item_id !== null ? $line->items[0]->sku : null), // Bundles don't have sku, must have item sku for old structure
                    'inventory_owner_id' => $inventoryOwner->id,
                    'inventory_owner_pid' => $inventoryOwner->pid,
                    'discount_amount' => 0.00, // TODO where can this come from
                    'discount_type_id' => null,
                    'variant' => ($line->item_id !== null && isset($line->items[0]->variant_name) ? $line->items[0]->variant_name : ''),
                    'option' => ($line->item_id !== null && isset($line->items[0]->option) ? $line->items[0]->option : ''),
                    'event_id' => (isset($line->event_id) ? $line->event_id : null),
                    'pid' => $line->orderline_pid,
                    'items' => json_encode($line->items),
                    'created_at' => $dateTime,
                    'updated_at' => $dateTime
                ];
                // For now we are packing bundle items into lines for old structure
                if (isset($line->bundle_id)) {
                    foreach ($line->items as $key => $item) {
                        $lines[] = [
                            'order_id' => $order->id,
                            'item_id' => $item->id,
                            'bundle_id' => $line->bundle_id,
                            'bundle_name' => $line->bundle_name,
                            'type' => 'Bundle',
                            'name' => $item->product_name,
                            'price' => 0.00,
                            'quantity' => $item->quantity * $line->quantity,
                            'manufacturer_sku' => $item->sku,
                            'inventory_owner_id' => $inventoryOwner->id,
                            'inventory_owner_pid' => $inventoryOwner->pid,
                            'discount_amount' => 0.00,
                            'discount_type_id' => null,
                            'variant' => $item->variant_name,
                            'option' => $item->option,
                            'event_id' => (isset($line->event_id) ? $line->event_id : null),
                            'pid' => null,
                            'items' => null,
                            'created_at' => $dateTime,
                            'updated_at' => $dateTime
                            ];
                    }
                }
            }
            app('db')->beginTransaction();
            Orderline::insert($lines);
            if ($order->lines->isEmpty()) {
                app('log')->error('Order lines was empty on checkout. Possible replication issue?', ['order' => $order]);
            }
            app('db')->commit();

            foreach ($affiliatePayouts as $payout) {
                CommissionReceipt::create([
                'order_id' => $order->id,
                'transaction_id' => $order->transaction_id,
                'user_id' => $payout['payeeUserId'],
                'amount' => $payout['amount'],
                ]);
            }

            $transaction = $this->paymanService->captureTransaction($order->transaction_id, $order->receipt_id);
            if ($transaction->resultCode != 1 || ($transaction->statusCode != 'P' && $transaction->statusCode != 'S')) {
                // Failed to authorize/validate payment
                abort(422, json_encode(['result_code' => 5, 'result' => 'Payment failed.', 'message' => $transaction->result, 'transaction' => $transaction]));
            }
            if ($checkout->transfer_pid) {
                if ($checkout->isWholesale() || $checkout->type === 'rep-transfer') {
                    if ($settings->inventory_confirmation->show) {
                        $this->inventoryService->transferReservation($checkout->transfer_pid, null, null);
                        $this->inventoryService->assureInventoryCreated($order); // Make sure rep gets empty inventory records
                        $order->inventory_received_at = null;
                        $order->save();
                    } else {
                        $this->inventoryService->transferReservation($checkout->transfer_pid, $buyer->id, $buyer->pid);
                    }
                } else {
                    $this->inventoryService->transferReservation($checkout->transfer_pid, null, null);
                }
            } elseif ($checkout->isWholesale() || $checkout->type === 'rep-transfer') {
                // Wholesale/transfer invoices will not have a reservation
                if ($settings->inventory_confirmation->show) {
                    $this->inventoryService->assureInventoryCreated($order); // Make sure rep gets empty inventory records
                    $order->inventory_received_at = null;
                } else {
                    if ($this->inventoryService->confirmInventoryForOrder($order) == null) {
                        $order->inventory_received_at = null;
                    }
                }
            }

            $order->paid_at = Carbon::now()->setTimezone('UTC')->toDateTimeString();
            $order->status = $this->getDefaultOrderStatus($checkout);
            if ($order->type_id === 11) {
                $order->comm_engine_status_id = 3;
            } elseif ($settings->use_commission_engine->value) {
                // Only set comm_engine_status_id if using commissions
                $order->comm_engine_status_id = 1;
            }
            $order->save();
            event(new \CPCommon\Events\GenericEvent(
                'order-created',
                [
                    'order' => $order
                ],
                $request->user->orgId,
                0
            ));
        } catch (\Exception $e) {
            if (!empty($order->id)) {
                try {
                    // if order was created soft delete it so we have the data to review if needed
                    Order::where('id', $order->id)->delete();
                    // Soft delete lines
                    Orderline::where('order_id', $order->id)->delete();
                    if (sizeof($affiliatePayouts) > 0) {
                        // Delete affiliate payouts
                        CommissionReceipt::where('order_id', $order->id)->delete();
                    }
                } catch (\Exception $e2) {
                    app('log')->error($e2);
                    // Just in case we have a database error we still want to attempt to refund the money
                }
            }
            // refund transaction
            $this->paymanService->cancelTransaction($transaction->id, $transaction->amount);
            if ($checkout->coupon_id != null) {
                // Revert coupon use
                if (!$couponRepo->subtractUse($checkout->coupon_id)) {
                    app('log')->error('Failed to return coupon use after order failure.', ['checkout' => $checkout, 'order' => $order]);
                }
            }
            $this->releaseEventItems($checkout);
            if ($e instanceof HttpException) {
                throw $e; // Forward the http error response if exists
            } else {
                app('log')->error($e);
                abort(500);
            }
        }
        try {
            $checkout->delete();
            if ($checkout->cart_pid) {
                $this->cartRepo->empty($checkout->cart_pid);
            }
            if ($checkout->invoice_id) {
                $updated = Invoice::where('id', '=', $checkout->invoice_id)->update(['order_id' => $order->id]);
                Invoice::where('id', '=', $checkout->invoice_id)->delete();
                if (!$updated) {
                    app('log')->error('Failed to update invoice with order', ['InvoiceId' => $checkout->invoice_id]);
                }
            }
        } catch (\Exception $e) {
            app('log')->error($e);
            // silent fail on this post order cleanup
        }
        return response()->json(['order' => $order, 'result_code' => 1, 'result' => 'Order created.'], 201);
    }

    private function createTaxes($checkout)
    {
        if (empty($checkout->shipping_address)) {
            $checkout->tax_invoice_pid = null;
            $checkout->tax = 0.00;
            return;
        }
        $businessAddress = $this->userService->getBusinessAddressForUser($checkout->inventory_user_pid);
        if (!isset($businessAddress)) {
            abort(501, 'Business Address not set for taxes. Contact store owner.');
        }
        $taxInvoice = $this->taxService->createInvoiceForCheckout($checkout, $businessAddress);
        $checkout->tax_invoice_pid = $taxInvoice->pid;
        $checkout->tax = $taxInvoice->tax;
    }

    private function getCreateSettings()
    {
        return $this->settingsService->getSettings(
            [
                'tax_calculation',
                'tax_exempt_wholesale',
                'wholesale_cart_min',
                'wholesale_cart_min_amount',
                'reseller_coupons',
                'corp_coupons'
            ]
        );
    }

    private function updateTaxInvoice($checkout, $data)
    {
        $taxInvoice = $this->taxService->updateInvoice($checkout->tax_invoice_pid, $data);
        $checkout->tax = $taxInvoice->tax;
    }

    private function validateCard($card)
    {
        if (!$this->validateCardNumber($card['number'])) {
            abort(422, json_encode(['card.number' => ['Invalid']]));
        }
        if (Carbon::now()->setTimezone('UTC')->gte(Carbon::create($card['year'], $card['month'], 1, 0, 0, 0)->endOfMonth())) {
            abort(422, json_encode(['card' => ['Expired']]));
        }
    }

    private function validateCardNumber($number)
    {
        // Check numbers only
        if (!is_numeric($number)) {
            return false;
        }
        // Luhn checksum
        // Set the string length and parity
        $number_length=strlen($number);
        if ($number_length < 13 || $number_length > 16) {
            return false;
        }
        $parity=$number_length % 2;

        // Loop through each digit and do the maths
        $total=0;
        for ($i=0; $i<$number_length; $i++) {
            $digit=$number[$i];
            // Multiply alternate digits by two
            if ($i % 2 == $parity) {
                $digit*=2;
                // If the sum is two digits, add them together (in effect)
                if ($digit > 9) {
                    $digit-=9;
                }
            }
            // Total up the digits
            $total+=$digit;
        }

        return ($total % 10 == 0);
    }

    private function parseOrderType($storeType, $seller, $buyer)
    {
        switch ($storeType) {
            case 'custom-wholesale':
            case 'wholesale':
                return 1;
            case 'retail':
            case 'custom-corp':
            case 'custom-retail':
                if (in_array($seller->role_id, [7,8])) {
                    if (in_array($buyer->role_id, [7,8])) {
                        return 5;
                    } else {
                        return 2;
                    }
                } else {
                    return 3;
                }
            case 'custom-personal':
                return 10;
            case 'affiliate':
            case 'custom-affiliate':
                return 9;
            case 'rep-transfer':
                return 11;
        }
    }

    private function checkEventItems($checkout)
    {
        app('db')->beginTransaction();
        foreach ($checkout->lines as $key => $line) {
            if (!empty($line->event_id)) {
                $results = app('db')->update(
                    'UPDATE events SET events.items_purchased = ? + events.items_purchased'.
                    ' WHERE (events.id = ? AND events.items_limit >= ? + events.items_purchased)'.
                    ' OR (events.id = ? AND events.items_limit IS NULL)',
                    [$line->quantity, $line->event_id, $line->quantity, $line->event_id]
                );
                if ($results < 1) {
                    app('db')->rollback();
                    $event = app('db')->table('events')->where('id', $line->event_id)->first();
                    return ['Event: '. $event->name .' is full. Items left for purchase: ' . (($event->items_limit) - ($event->items_purchased))];
                }
            }
        }
        app('db')->commit();
        return [];
    }

    private function releaseEventItems($checkout)
    {
        foreach ($checkout->lines as $key => $line) {
            if (!empty($line->event_id)) {
                app('db')->update(
                    'UPDATE events SET events.items_purchased = events.items_purchased - ?'.
                    ' WHERE events.id = ?',
                    [$line->quantity, $line->event_id]
                );
            }
        }
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

    private function recalculateCheckout($checkout, $settings)
    {
        // If using self pickup and settings are correct with associated checkout type, set shipping to 0
        if ($checkout->self_pickup && (
            ($settings->self_pickup_wholesale->show && $checkout->isWholesale()) ||
            ($settings->self_pickup_reseller->show && $checkout->type === 'retail')
        )) {
            $checkout->shipping = 0;
        // If shipping was previously calculated, we should re-calculate
        } elseif ($checkout->shipping_rate_id) {
            // Shipping is calculated from discounted amount
            $shippingRate = $this->shippingService->findRate($checkout->inventory_user_pid, $checkout->type, $checkout->subtotal);
            if ($shippingRate == null) {
                abort(501, 'Shipping rates have not been set. Contact store owner to resolve this issue.');
            }
            $checkout->shipping_rate_id = $shippingRate->id;
            $checkout->shipping = $shippingRate->amount + $checkout->getPremiumShipping();
        }
        if (!$checkout->tax_exempt && filter_var($settings->tax_calculation->show, FILTER_VALIDATE_BOOLEAN)
            && (!$checkout->isWholesale() || !filter_var($settings->tax_exempt_wholesale->show, FILTER_VALIDATE_BOOLEAN))) {
            // Calculate taxes if turned on unless wholesale is exempt
            if (isset($checkout->tax_invoice_pid)) {
                $this->taxService->deleteTaxInvoice($checkout->tax_invoice_pid);
            }
            $this->createTaxes($checkout);
        } else {
            $checkout->tax_invoice_pid = null;
            $checkout->tax = 0.00;
        }

        $checkout->total = round($checkout->subtotal - $checkout->discount + $checkout->shipping + $checkout->tax, 2);
        $checkout->save();
    }

    private function inventorySync($checkout, $inventoryTransfer, $settings)
    {
        if (empty($inventoryTransfer->errors)) {
            // No problems
            return;
        }
        if (empty($inventoryTransfer->reservations)) {
            // No product available
            $checkout->delete();
            abort(
                422,
                json_encode([
                    'checkout' => null,
                    'result_code' => 3,
                    'result' => 'Inventory unavailable.',
                    'message' => 'All requested inventory unavailable.'
                ])
            );
        }
        $bundleLinePids = []; // Filter additional bundle items
        $lines = []; // Re-create lines using partial results
        foreach ($inventoryTransfer->reservations as $key => $reservation) {
            $line = $checkout->findLineForPid($reservation->transaction_id);
            if (!$line) {
                app('log')->error('Reserved line not in checkout lines', ['checkout' => $checkout, 'transaction_id' => $reservation->transaction_id, '$inventoryTransfer' => $inventoryTransfer, 'lines' => $lines]);
                abort(500);
            }
            $line->quantity = $reservation->quantity; // Update quantity for line
            if (isset($line->item_id)) {
                $lines[] = $line;
            } else {
                // Bundle is different
                if (!in_array($reservation->transaction_id, $bundleLinePids)) {
                    $bundleLinePids[] = $reservation->transaction_id;
                    $lines[] = $line;
                }
            }
        }
        $checkout->lines = $lines;
        $checkout->subtotal = $checkout->calculateSubtotal();
        if (isset($checkout->coupon)) {
            $checkout->discount = $checkout->coupon->calculateDiscount($checkout->subtotal);
        }
        if ($checkout->discount > $checkout->subtotal) {
            $checkout->discount = $checkout->subtotal;
        }
        $this->recalculateCheckout($checkout, $settings);
        abort(
            422,
            json_encode([
                'checkout' => $checkout,
                'result_code' => 4,
                'result' => 'Inventory partially available. Checkout updated to remaining product.',
                'message' => 'Some product unavailable. Automatically updated checkout to reflect available product.'
            ])
        );
    }

    private function convertInvoiceOrderTypeToCheckoutType($typeId)
    {
        switch ($typeId) {
            case 1:
                return 'wholesale'; // Make it no longer custom
            case 9:
                return 'affiliate';
            case 11:
                return 'rep-transfer';
            default:
                return 'retail';
        }
    }

    private function calculatePaymentDetails($type, $seller, $inventoryOwner, $buyer, $affiliateTeam)
    {
        $details = [];
        switch ($type) {
            case 'custom-personal':
                $details['teamId'] = 'rep';
                $details['description'] = 'Personal Use';
                break;
            case 'retail':
            case 'custom-retail':
                $details['teamId'] = (in_array($inventoryOwner->role_id, [7,8]) ? 'company' : 'rep');
                $details['description'] = CheckoutController::ROLE_DESC[$seller->role_id] . ' to ' . CheckoutController::ROLE_DESC[$buyer->role_id];
                break;
            case 'affiliate':
            case 'custom-affiliate':
                $details['teamId'] = $affiliateTeam;
                $details['description'] = 'Affiliate';
                break;
            case 'custom-wholesale':
            case 'wholesale':
                $details['teamId'] = 'wholesale'; // e-wallet payments will switch this to rep
                $details['description'] = 'Corporate to Rep';
                break;
            case 'custom-corp':
                $details['teamId'] = 'company';
                $details['description'] = 'Corporate to ' . CheckoutController::ROLE_DESC[$buyer->role_id];
                break;
            case 'rep-transfer':
                $details['teamId'] = 'rep';
                $details['description'] = 'Rep Transfer';
                break;
            default:
                return null;
        }
        return $details;
    }

    private function getDefaultOrderStatus($checkout)
    {
        if ($checkout->self_pickup) {
            return 'self-pickup';
        }
        switch ($checkout->type) {
            case 'custom-personal':
                return 'fulfilled';
            default:
                return 'unfulfilled';
        }
    }
}
