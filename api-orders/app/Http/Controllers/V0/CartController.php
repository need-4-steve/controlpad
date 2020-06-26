<?php

namespace App\Http\Controllers\V0;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Repositories\EloquentV0\CartRepository;
use App\Repositories\EloquentV0\CouponRepository;
use App\Services\InventoryServiceInterface;
use App\Services\SettingsServiceInterface;
use App\Services\UserServiceInterface;
use App\Cart;
use App\Cartline;
use App\Coupon;

class CartController extends Controller
{
    private $cartRepo;
    private $inventoryService;
    private $settingsService;
    private $userService;

    public function __construct(
        InventoryServiceInterface $inventoryService,
        SettingsServiceInterface $settingsService,
        UserServiceInterface $userService
    ) {
        $this->cartRepo = new CartRepository;
        $this->inventoryService = $inventoryService;
        $this->settingsService = $settingsService;
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        $this->validate($request, ['page' => 'sometimes|integer', 'per_page' => 'sometimes|integer']);
        $params = $request->only(Cart::$indexParams);
        if (!$request->user->hasRole(['Admin', 'Superadmin'])) {
            // Restrict buyer id to authed user if not an admin
            $params['buyer_pid'] = $request->user->pid;
        }

        return response()->json($this->cartRepo->index($params));
    }

    public function show(Request $request, $pid)
    {
        $params = $request->only(['expands']);
        $cart = $this->cartRepo->cartByPid($pid, $params);
        if ($cart != null) {
            if (!$request->user->hasRole(['Admin', 'Superadmin']) && $cart->buyer_pid != $request->user->pid) {
                abort(403, 'Buyer or admin only');
            }
        } else {
            abort(404, 'Cart not found');
        }
        return response()->json($cart);
    }

    public function create(Request $request)
    {
        $this->validate($request, Cart::$createRules);
        $cart = $request->only(array_keys(Cart::$createRules));
        $isAdmin = $request->user->hasRole(['Admin', 'Superadmin']);
        $companyPid = $this->settingsService->getCompanyPid();
        if (isset($cart['buyer_pid']) && !$isAdmin && $request->user->pid != $cart['buyer_pid']) {
            abort(403, 'Buyer or admin only');
        }
        switch ($cart['type']) {
            case 'custom-wholesale':
                // For now these are the same validation
            case 'custom-corp':
                if (!$isAdmin) {
                    abort(403, 'custom-corp type is admin only');
                }
                if ($cart['seller_pid'] != $companyPid || $cart['inventory_user_pid'] !== $companyPid) {
                    return response()->json(['type' => [ $cart->type . ' type with company seller only']], 422);
                }
                break;
            case 'wholesale':
                // TODO check if user is an affiliate and if they are allowed to buy product
                if ($cart['seller_pid'] != $companyPid || $cart['inventory_user_pid'] !== $companyPid) {
                    return response()->json(['type' => ['wholesale type only with company seller pid']], 422);
                }
                if (!$request->user->hasRole(['Rep'])) {
                    abort(403, 'Wholesale purchase is Rep only');
                }
                break;
            case 'rep-transfer':
                if ($request->user->pid != $cart['seller_pid']) {
                    abort(403, $cart['type'].' cart can only be operated by seller or admin');
                }
                if (!$request->user->hasRole(['Rep'])) {
                    abort(403, 'Rep transfer purchase is Rep only');
                }
                break;
            case 'custom-affiliate':
                if (!$isAdmin && $request->user->pid !== $cart['seller_pid']) {
                    abort(403, $cart['type'].' cart can only be operated by seller or admin');
                }
                // rest is like normal affiliate for now
            case 'affiliate':
                if ($cart['seller_pid'] == $cart['inventory_user_pid']) {
                    return response()->json(['type' => ['affiliate cart only works if seller is different than inventory owner']], 422);
                }
                if ($cart['inventory_user_pid'] !== $companyPid) {
                    return response()->json(['type' => ['affiliate cart only works with company seller id']], 422);
                }
                break;
            case 'custom-personal':
                if ($cart['inventory_user_pid'] === $companyPid) {
                    return response()->json(['type' => ['Personal consumption is not for corp']], 422);
                }
                // For now the custom-retail checks are the same as custom-personal checks
            case 'custom-retail':
                if ($request->user->hasRole(['Admin', 'Superadmin'])) {
                    $ownerPid = $this->settingsService->getCompanyPid();
                } else {
                    $ownerPid = $request->user->pid;
                }
                if (!$isAdmin && $ownerPid != $cart['seller_pid']) {
                    abort(403, $cart['type'].' cart can only be operated by seller or admin');
                }
                if (!$isAdmin && !$request->user->hasRole(['Rep'])) {
                    abort(403, 'custom-personal cart can only be operated by rep or admin');
                }
                if ($cart['seller_pid'] != $cart['inventory_user_pid']) {
                    return response()->json(['type' => ['Seller and Inventory owner must be the same for type: '.$cart['type']]], 422);
                }
                break;
            default:
                if ($cart['seller_pid'] != $cart['inventory_user_pid']) {
                    return response()->json(['type' => ['Seller and Inventory owner must be the same for type: ' . $cart['type']]], 422);
                }
                break;
        }
        return response()->json($this->cartRepo->create($cart));
    }

    public function delete(Request $request, $pid)
    {
        $isAdmin = $request->user->hasRole(['Admin', 'Superadmin']);
        $cart = Cart::select('id', 'buyer_pid')->where('pid', $pid)->first();
        if (isset($cart->buyer_pid) && !$isAdmin && $request->user->pid != $cart->buyer_pid) {
            abort(403, 'Buyer or admin only');
        }
        $cart->delete();
        return response()->json('', 200);
    }

    public function addLines(Request $request, $pid)
    {
        return $this->updateLines($request, $pid, false);
    }

    public function patchLines(Request $request, $pid)
    {
        return $this->updateLines($request, $pid, true);
    }

    private function updateLines(Request $request, $pid, $quantityReplace)
    {
        $this->validate($request, Cartline::$createRules);
        $lines = $request->all();
        if (sizeof($lines) == 0) {
            abort(400, 'Request body empty');
        }
        $isAdmin = $request->user->hasRole(['Admin', 'Superadmin']);

        $cart = $this->cartRepo->cartByPid($pid, ['expands' => ['lines']]);
        if (!$cart) {
            abort(404, 'Cart not found');
        }
        if (isset($cart['buyer_pid']) && !$isAdmin && $request->user->pid != $cart['buyer_pid']) {
            abort(403, 'Buyer or admin only');
        }

        $itemIds = [];
        $bundleIds = [];
        // update quantity or collect ids for new lines
        foreach ($lines as $key => $line) {
            $line['cart_id'] = $cart->id;
            if (isset($line['item_id'])) {
                $itemIds[] = $line['item_id'];
            } elseif (isset($line['bundle_id'])) {
                $bundleIds[] = $line['bundle_id'];
            }
        }

        // Find and map items and bundles
        $itemMap = [];
        if (sizeof($itemIds) > 0) {
            $items = $this->inventoryService->getInventories($itemIds, $cart->inventory_user_pid)->data;
            foreach ($items as $key => $item) {
                $itemMap[$item->id] = $item;
            }
        }

        $bundleMap = [];
        if (sizeof($bundleIds) > 0) {
            if (!$cart->isWholesale()) {
                abort(400, 'Bundles only allowed with wholesale');
            }
            $bundles = $this->inventoryService->getBundles($bundleIds, $cart->inventory_user_pid)->data;
            foreach ($bundles as $key => $bundle) {
                $bundleMap[$bundle->id] = $bundle;
            }
        }

        foreach ($lines as $lineKey => $line) {
            $cartLine = new Cartline($line);
            if ($cartLine->item_id != null) {
                if (empty($cartLine->event_id)) {
                    $itemLine = $cart->lines->where('item_id', '=', $cartLine->item_id)->first();
                } else {
                    $itemLine = $cart->lines->where('item_id', '=', $cartLine->item_id)->where('event_id', '=', $cartLine->event_id)->first();
                }
                if ($itemLine != null) {
                    if ($quantityReplace) {
                        $itemLine->quantity = $cartLine->quantity;
                    } else {
                        $itemLine->quantity += $cartLine->quantity;
                    }
                    $itemLine->save();
                    continue;
                } elseif (array_key_exists($cartLine->item_id, $itemMap)) {
                    $item = $itemMap[$cartLine->item_id];
                    $cartLine->inventory_owner_pid = $item->owner_pid;
                    $cartLine->price = round($this->parseItemPrice($cart, $line, $item), 2);
                    $cartLine->bundle_id = null;
                    $cartLine->bundle_name = null;
                    $cartLine->tax_class = $item->variant->product->tax_class;
                    // Single item in the items array
                    if (isset($item->variant->images[0]->url)) {
                        $itemImageUrl = $item->variant->images[0]->url;
                    } elseif (isset($item->variant->product->images[0]->url)) {
                        $itemImageUrl = $item->variant->product->images[0]->url;
                    } else {
                        $itemImageUrl = null;
                    }
                    $cartLine->items = [[
                        'id' => $item->id,
                        'inventory_id' => $item->inventory_id,
                        'product_name' => $item->variant->product->name,
                        'variant_name' => $item->variant->name,
                        'option_label' => $item->variant->option_label,
                        'option' => $item->option,
                        'sku' => $item->sku,
                        'weight' => $item->weight,
                        'premium_shipping_cost' => $item->premium_shipping_cost,
                        'img_url' => $itemImageUrl,
                        'variant_label' => $item->variant->product->variant_label
                    ]];
                } else {
                    app('log')->error('Failed to add item to cart', ['item_id' => $cartLine->item_id, 'cart_pid' => $cart->pid]);
                    abort(500);
                }
            } else {
                $bundleLine = $cart->lines->where('bundle_id', '=', $cartLine->bundle_id)->first();
                if ($bundleLine != null) {
                    $bundleLine->quantity += $cartLine->quantity;
                    $bundleLine->save();
                    continue;
                } elseif (array_key_exists($cartLine->bundle_id, $bundleMap)) {
                    $bundle = $bundleMap[$cartLine->bundle_id];
                    $cartLine->inventory_owner_pid = $bundle->user_pid;
                    $cartLine->price = $bundle->wholesale_price;
                    $cartLine->item_id = null;
                    $cartLine->bundle_name = $bundle->name;
                    $cartLine->tax_class = $bundle->tax_class;
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
                    $cartLine->items = $filterItems;
                } else {
                    app('log')->error('Failed to add bundle to cart.', ['cart_pid' => $cart->pid, 'bundle_id' => $cartLine->bundle_id]);
                    abort(500);
                }
            }

            $cartLine->cart_id = $cart->id;
            $cartLine->pid = \CPCommon\Pid\Pid::create();
            $cartLine->created_at = date('Y-m-d H:i:s');
            $cartLine->updated_at = date('Y-m-d H:i:s');
            $cartLine->save();
            $cart->lines->push($cartLine);
            $cart->touch();
        }
        return response()->json($cart);
    }

    public function patchCartline(Request $request, $pid)
    {
        $cartline = Cartline::where('pid', $pid)->first();
        if (!$cartline) {
            abort(404, 'No cartline found');
        }

        $isAdmin = $request->user->hasRole(['Admin', 'Superadmin']);
        $cart = Cart::select('id', 'type', 'buyer_pid')->where('id', $cartline->cart_id)->first();
        if (isset($cart->buyer_pid) && !$isAdmin && $request->user->pid != $cart->buyer_pid) {
            abort(403, 'Buyer or admin only');
        }

        if ($request->has('quantity')) {
            // TODO check min/max, this needs added or pulled for items, related to cart type wholesale
            $cartline->quantity = $request->input('quantity');
            $cartline->save();
            $cart->touch();
        }
        return response()->json($cartline);
    }

    public function deleteCartline(Request $request, $pid)
    {
        $cartline = Cartline::where('pid', $pid)->first();
        if (!$cartline) {
            abort(400, 'No cartline found');
        }

        $isAdmin = $request->user->hasRole(['Admin', 'Superadmin']);
        $cart = Cart::select('id', 'buyer_pid')->where('id', $cartline->cart_id)->first();
        if (isset($cart->buyer_pid) && !$isAdmin && $request->user->pid != $cart->buyer_pid) {
            abort(403, 'Buyer or admin only');
        }

        $cartline->delete();
        $cart->touch();
        return response()->json('', 200);
    }

    public function empty(Request $request, $pid)
    {
        $isAdmin = $request->user->hasRole(['Admin', 'Superadmin']);
        $cart = Cart::where('pid', $pid)->first();
        if (!$isAdmin && isset($cart->buyer_pid) && $request->user->pid != $cart->buyer_pid) {
            abort(403, 'Buyer or admin only');
        }
        $this->cartRepo->empty($pid);
        $cart->setAttribute('lines', []);
        return response()->json($cart, 200);
    }

    public function updateCart(Request $request)
    {
        // TODO allow custom discount and shipping for custom-corp and custom-retail?
    }

    public function applyCoupon(Request $request, $pid)
    {
        $couponRepo = new CouponRepository;
        $this->validate($request, ['code' => Coupon::$createRules['code']]);
        $isAdmin = $request->user->hasRole(['Admin', 'Superadmin']);
        $couponCode = $request->input('code');

        $cart = $this->cartRepo->cartByPid($pid);
        if (!$cart) {
            abort(400, 'No cart found');
        }
        if (isset($cart['buyer_pid']) && !$isAdmin && $request->user->pid != $cart['buyer_pid']) {
            abort(403, 'Buyer or admin only');
        }
        $coupon = $couponRepo->couponByCode($couponCode, $cart->inventory_user_pid);
        if ($coupon == null) {
            return response()->json(['code' => ['Coupon code not available.']], 422);
        }
        // Check for customer coupon restriction
        if ($coupon->customer_id != null &&
            ($cart->buyer_pid == null || $this->userService->getUserbyPid($cart->buyer_pid)->id !== $coupon->customer_id)
        ) {
            abort(422, json_encode(['result_code' => 9, 'result' => 'Coupon customer and buyer must be the same', 'message' => 'Coupon customer and buyer must be the same']));
        }
        switch ($cart->type) {
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
        //If cart already has a coupon we are limitting to 1 until more defined restrictions
        if ($coupon->isExpired()) {
            return response()->json(['code' => ['Coupon is expired.']], 422);
        }
        if ($coupon->uses >= $coupon->max_uses) {
            return response()->json(['code' => ['Coupon use limit exceeded']], 422);
        }
        $cart->coupon_id = $coupon->id;
        $cart->save();
        $cart->setAttribute('coupon', $coupon);

        return response()->json($cart);
    }

    public function estimateShipping(Request $request, $pid)
    {
        $isAdmin = $request->user->hasRole(['Admin', 'Superadmin']);

        $cart = $this->cartRepo->cartByPid($pid);
        if (!$cart) {
            abort(400, 'No cart found');
        }
        if (!$isAdmin && isset($cart['buyer_pid']) && $request->user->pid != $cart['buyer_pid']) {
            abort(403, 'Buyer or admin only');
        }
        $subtotal = $cart->calculateSubtotal();

        $shippingService = app()->make(\App\Services\ShippingServiceInterface::class);
        $shippingRate = $shippingService->findRate($cart->inventory_user_pid, $cart->type, $subtotal);
        if ($shippingRate == null) {
            abort(501, 'Shipping rates have not been set.');
        }
        $shipping = $shippingRate->amount + $cart->getPremiumShipping();
        $cart->touch();

        return response()->json(['shipping' => $shipping]);
    }

    private function parseItemPrice($cart, $line, $item)
    {
        switch ($cart->type) {
            case 'custom-corp':
                if (isset($line['price'])) {
                    // Allow price to be set for this cart type
                    return $line['price'];
                }
                // Default price if no custom is defined
                return (round($item->inventory_price, 2) > 0.00 ? $item->inventory_price : $item->retail_price);
                break;
            case 'wholesale':
                // Check min max requirements
                if ($item->variant->product->min && $item->variant->product->min > $line['quantity']) {
                    abort(400, 'You need a minimum quantity of ' . $item->variant->product->min);
                } elseif ($item->variant->product->max && $item->variant->product->max < $line['quantity']) {
                    abort(400, 'Your maximum quantity is ' . $item->variant->product->max);
                } elseif ($item->variant->min && $item->variant->min > $line['quantity']) {
                    abort(400, 'You need a minimum quantity of ' . $item->variant->min);
                } elseif ($item->variant->max && $item->variant->max < $line['quantity']) {
                    abort(400, 'Your maximum quantity is ' . $item->variant->max);
                }
                // custom wholesale doesn't require min/max
            case 'custom-wholesale':
                return $item->wholesale_price;
                break;
            case 'rep-transfer':
                return $item->wholesale_price;
                break;
            case 'custom-personal':
                // Used for a rep to pay taxes on the wholesale price
                return $item->wholesale_price;
                break;
            case 'retail':
            case 'affiliate':
            case 'custom-affiliate':
            case 'custom-retail':
                $companyPid = $this->settingsService->getCompanyPid();
                if ($cart->inventory_user_pid === $companyPid) {
                    // company retail uses premium price for now, but might move to inventory price later
                    if (round($item->inventory_price, 2) > 0.00) {
                        return $item->inventory_price;
                    }
                    if ($cart->type === 'retail' && round($item->premium_price, 2) > 0.00) {
                        return $item->premium_price;
                    }
                } elseif (isset($item->inventory_price)) {
                    // Check if rep price is allowed
                    $repPriceAllowed = $this->settingsService->getSettings(['rep_custom_prices']);
                    if (isset($repPriceAllowed->rep_custom_prices) && $repPriceAllowed->rep_custom_prices->show == true) {
                        return $item->inventory_price;
                    }
                }
                // Default to retail price if premium price and inventory price are not used
                return $item->retail_price;
                break;
            default:
                break;
        }
    }
}
