<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\CartRepository;
use App\Repositories\Eloquent\ItemRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\CouponAppliedRepository;
use App\Services\Cart\WholesaleCartService;
use App\Services\Cart\CustomCartService;
use Illuminate\Http\Request;
use App\Models\Address;
use App\Models\User;
use App\Models\Cart;
use CPCommon\Pid\Pid;

class CartController extends Controller
{
    /* @var CartRepository */
    protected $cartRepo;
    protected $itemRepo;
    protected $userRepo;
    protected $couponAppliedRepo;
    protected $wholesaleCartService;
    protected $customCartService;

    /**
     * Create a new controller instance.
     *
     * @param CartRepository  $cartRepo
     * @param ItemRepository  $itemRepo
     * @param UserRepository  $userRepo
     * @param CouponAppliedRepository  $couponAppliedRepo
     * @return void
     */
    public function __construct(
        CartRepository $cartRepo,
        WholesaleCartService $wholesaleCartService,
        CustomCartService $customCartService,
        ItemRepository $itemRepo,
        UserRepository $userRepo,
        CouponAppliedRepository $couponAppliedRepo
    ) {
        $this->cartRepo = $cartRepo;
        $this->itemRepo = $itemRepo;
        $this->userRepo = $userRepo;
        $this->couponAppliedRepo = $couponAppliedRepo;
        $this->wholesaleCartService = $wholesaleCartService;
        $this->customCartService = $customCartService;
    }

    public function create()
    {
        $storeOwner = $this->getStoreOwner();

        // Create a cart
        $cartData = [];
        $cartData['pid'] = Pid::create();
        $cartData['buyer_pid'] = null; // We can't have a buyer on the store front because the auth is different in checkout

        if ($storeOwner->hasRole(['Superadmin', 'Admin'])) {
            // Corp store
            $cartData['type'] = 'retail';
            $cartData['seller_pid'] = $storeOwner->pid;
            $cartData['inventory_user_pid'] = $storeOwner->pid;
        } elseif ($storeOwner->hasSellerType(['Affiliate'])) {
            // Affiliate storefront
            $companyPid = User::select('pid')
            ->where('id', config('site.apex_user_id'))->first()->pid;

            $cartData['type'] = 'affiliate';
            $cartData['seller_pid'] = $storeOwner->pid;
            $cartData['inventory_user_pid'] = $companyPid;
        } else {
            // Reseller storefront
            $cartData['type'] = 'retail';
            $cartData['seller_pid'] = $storeOwner->pid;
            $cartData['inventory_user_pid'] = $storeOwner->pid;
        }

        $cart = Cart::create($cartData);

        // set response object
        if (!empty($cart)) {
            return response()->json($cart, 200);
        }
    }

    public function getByPid($pid)
    {
        $cart = Cart::with('lines')->where('pid', '=', $pid)->first();
        if (empty($cart)) {
            abort(404, 'Cart not found');
        }
        return response()->json($cart, 200);
    }

    public function addItem($pid)
    {
        $cart = Cart::with('lines')->where('pid', '=', $pid)->first();
        if (empty($cart)) {
            abort(404, 'Cart not found');
        }

        $data = request()->all();
        if (!isset($data['quantity'])) {
            return response()->json(['error' => true, 'message' =>'You need to add a quantity'], 400);
        }
        if (empty($data['item_id'])) {
            return response()->json(['error' => true, 'message' => 'item_id missing'], 400);
        }
        $eventId = request()->get('event_id');

        // If item is already in cart, then update quantity
        if ($cartline = $cart->lines->where('item_id', $data['item_id'])->first()) {
            $cartline->quantity += $data['quantity'];
            if (!empty($eventId)) {
                $cartline->event_id = $eventId;
            }
            if ($cartline->quantity <= 0) {
                $cartline->delete();  // TODO this might not update the cart that is returned
            } else {
                $cartline->save();
            }
        } else {
            if ($data['quantity'] <= 0) {
                // Quantity can only be zero for existing lines to delete
                return response()->json(['error' => true, 'message' => 'Quantity must be greater than zero'], 400);
            }

            // When adding new line, pull item data from inventory api
            $inventoryUrl = ENV('INVENTORY_API_URL', 'https://inventory.controlpadapi.com/api/v0');
            $client = new \GuzzleHttp\Client;
            try {
                $response = $client->get(
                    $inventoryUrl . '/items/' . $data['item_id'],
                    [
                        'query' => [
                            'user_pid' => $cart->inventory_user_pid,
                            'expands' => [
                                'variant', 'product', 'variant_images', 'product_images'
                            ]
                        ],
                        'headers' => [
                            'Authorization' => 'Bearer ' . \App\Services\Authentication\JWTAuthService::getApiJWT()
                        ]
                    ]
                );
                $item = json_decode($response->getBody());
            } catch (\GuzzleHttp\Exception\RequestException $re) {
                app('log')->error($re);
                abort(500);
            }

            $inventoryOwner = User::where('pid', '=', $cart->inventory_user_pid)->first();
            if ($inventoryOwner->hasRole(['Rep']) && app('globalSettings')->getGlobal('rep_custom_prices', 'show') === true && !empty($item->inventory_price)) {
                $price = $item->inventory_price;
            } else {
                $price = $item->retail_price;
            }
            // Find image url
            if (isset($item->variant->images[0]->url)) {
                $itemImageUrl = $item->variant->images[0]->url;
            } elseif (isset($item->variant->product->images[0]->url)) {
                $itemImageUrl = $item->variant->product->images[0]->url;
            } else {
                $itemImageUrl = null;
            }
            $cart->lines()->create([
                'pid' => \CPCommon\Pid\Pid::create(),
                'item_id' => $item->id,
                'quantity' => $data['quantity'],
                'price' => $price,
                'inventory_owner_id' => $inventoryOwner->id,
                'inventory_owner_pid' => $inventoryOwner->pid,
                'event_id' => $eventId,
                'tax_class' => $item->variant->product->tax_class,
                // Display info used for new checkout/orders
                'items' => [
                    [
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
                    ]
                ]
            ]);
        }

        return response()->json($cart, 200);
    }

    public function updateItem($pid)
    {
        $data = request()->all();
        if (!isset($data['quantity'])) {
            return response()->json(['error' => true, 'message' =>'You need to add a quantity'], 400);
        }
        if (empty($data['item_id'])) {
            return response()->json(['error' => true, 'message' => 'item_id missing'], 400);
        }
        if ($data['quantity'] <= 0) {
            return response()->json(['error' => true, 'message' => 'quantity must be greater than zero'], 400);
        }

        $cart = Cart::with('lines')->where('pid', '=', $pid)->first();
        if (empty($cart)) {
            return response(404, 'Cart not found');
        }
        $line = $cart->lines->where('item_id', '=', $data['item_id'])->first();
        if ($line != null) {
            $line->quantity = $data['quantity'];
            $line->save();
        }

        return response()->json($cart, 200);
    }

    public function removeItem($pid, $itemID)
    {
        $cart = Cart::with('lines')->where('pid', '=', $pid)->first();
        if (empty($cart)) {
            return response(404, 'Cart not found');
        }

        $line = $cart->lines->where('item_id', '=', $itemID)->first();
        if ($line !== null) {
            $line->delete();
        }
        return response(204);
    }



    // !!!! DEPRICATED !!!!!!

    /**
     * Get cart or create a new one.
     *
     * @param Int $uid
     * @return Response
     */
    public function postShow($uid = null) // needs to be post method because the URI is too long for the server
    {
        // Depricated
        $pieces = explode('.', request()->getHost());
        $subdomain = $pieces[0];
        $storeDomain = env('STORE_DOMAIN');

        $storeOwner = session()->get('store_owner'); // Only works for replicated sites
        // Make sure store owner isn't wrong
        if ($storeOwner && $storeOwner->public_id !== $subdomain) {
            app('log')->error('Store owner not selected properly for postShow()', ['storeOwner' => $storeOwner, 'subdomain' => $subdomain]);
            return response()->json(['Unexpected error.'], 500);
        }

        $cart = session()->get('cart');
        if (!$cart) {
            // Can't auto create a cart for public checkout
            if ($subdomain == 'cart') {
                return response()->json(['Cart not found'], 404);
            }
            // Create a cart
            $cartData = [];
            $cartData['pid'] = Pid::create();
            $cartData['buyer_pid'] = null; // We can't have a buyer on the store front because the auth is different in checkout

            $companyPid = User::select('pid')
            ->where('id', config('site.apex_user_id'))->first()->pid;

            if ($storeOwner == null) {
                $cartData['seller_pid'] = $companyPid;
                $cartData['type'] = 'retail';
                $cartData['inventory_user_pid'] = $companyPid;
            } elseif ($storeOwner->hasSellerType(['Affiliate'])) {
                $cartData['seller_pid'] = $storeOwner->pid;
                $cartData['type'] = 'affiliate';
                $cartData['inventory_user_pid'] = $companyPid;
            } else {
                $cartData['seller_pid'] = $storeOwner->pid;
                $cartData['type'] = 'retail';
                $cartData['inventory_user_pid'] = $cartData['seller_pid'];
            }

            $cart = Cart::create($cartData);
        }

        // update cart total pricing
        if (!empty($request)) {
            $cart = $this->cartRepo->updateTotals($cart, $request, $type);
        } else {
            $cart = $this->cartRepo->updateTotals($cart, null, $type);
        }

        // return an error if there is an error
        if (is_array($cart) && isset($cart['error'])) {
            return response()->json($cart['error'], 422);
        }

        // set response object
        if (!empty($cart)) {
            session()->put('cart', $cart);
            return response()->json(['data' => $cart], 200);
        }

        return response()->json(['Not authorized to view this cart.'], 403);
    }

    /**
     * Add item to cart.
     *
     * @return Response
     */
    public function patchIndex()
    {
        $data = request()->all();
        if (!isset($data['quantity'])) {
            return response()->json(['error' => true, 'message' =>'You need to add a quantity'], 400);
        }
        if (empty($data['item_id'])) {
            return response()->json(['error' => true, 'message' => 'item_id missing'], 400);
        }
        $eventId = request()->get('event_id');

        $cart = $this->cartRepo->show();
        // If item is already in cart, then update quantity
        if ($cartline = $cart->lines->where('item_id', $data['item_id'])->where('event_id', $eventId)->first()) {
            $cartline->quantity += $data['quantity'];
            $cartline->event_id = $eventId;
            if ($cartline->quantity <= 0) {
                $cartline->delete();
            } else {
                $cartline->save();
            }
        } else {
            if ($data['quantity'] <= 0) {
                // Quantity can only be zero for existing lines to delete
                return response()->json(['error' => true, 'message' => 'Quantity must be greater than zero'], 400);
            }

            if (session()->has('store_owner') and session()->get('store_owner.seller_type_id') !== 1) {
                $inventoryOwner = session()->get('store_owner');
            } else {
                $inventoryOwner = $this->userRepo->find(config('site.apex_user_id'));
            }
            // When adding new line, pull item data from inventory api
            $inventoryUrl = ENV('INVENTORY_API_URL', 'https://inventory.controlpadapi.com/api/v0');
            $client = new \GuzzleHttp\Client;
            try {
                $response = $client->get(
                    $inventoryUrl . '/items/' . $data['item_id'],
                    [
                        'query' => [
                            'user_pid' => $inventoryOwner->pid,
                            'expands' => [
                                'variant', 'product', 'variant_images', 'product_images'
                            ]
                        ],
                        'headers' => [
                            'Authorization' => 'Bearer ' . \App\Services\Authentication\JWTAuthService::getApiJWT()
                        ]
                    ]
                );
                $item = json_decode($response->getBody());
            } catch (\GuzzleHttp\Exception\RequestException $re) {
                app('log')->error($re);
                abort(500);
            }

            // Figure out price
            if ($inventoryOwner->id === config('site.apex_user_id')) {
                // Corp sales are price premium or retail
                if (!empty($item->premium_price)) {
                    $price = $item->premium_price;
                } else {
                    $price = $item->retail_price;
                }
            } else {
                // Rep prices default to inventory or retail
                if (app('globalSettings')->getGlobal('rep_custom_prices', 'show') === true && !empty($item->inventory_price)) {
                    $price = $item->inventory_price;
                } else {
                    $price = $item->retail_price;
                }
            }
            // Find image url
            if (isset($item->variant->images[0]->url)) {
                $itemImageUrl = $item->variant->images[0]->url;
            } elseif (isset($item->product->images[0]->url)) {
                $itemImageUrl = $item->variant->images[0]->url;
            } else {
                $itemImageUrl = null;
            }
            $cart->lines()->create([
                'pid' => \CPCommon\Pid\Pid::create(),
                'item_id' => $item->id,
                'quantity' => $data['quantity'],
                'price' => $price,
                'inventory_owner_id' => $inventoryOwner->id,
                'inventory_owner_pid' => $inventoryOwner->pid,
                'event_id' => $eventId,
                'tax_class' => $item->variant->product->tax_class,
                // Display info used for new checkout/orders
                'items' => json_encode([
                    [
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
                    ]
                ])
            ]);
        }

        if ($cart = $this->cartRepo->updateTotals($cart)) {
            if ($cart['error']) {
                return response()->json(['error' => true, 'message' => $cart['error']['description']], 400);
            }
            session()->put('cart', $cart);
            return response()->json($cart, 200);
        }
        return response()->json(['message' => 'Failed to add to the cart'], 400);
    }

    /**
     * Add multiple items to a wholesale cart.
     *
     * @return Response
     */
    public function createWholesaleCartlines(Request $request)
    {
        $cart = $this->cartRepo->show();
        $inputs = $request->all();
        $addStatus = $this->wholesaleCartService->processNewCartlines($inputs);
        if ($addStatus !== true) {
            return response()->json($addStatus, 422);
        } else {
            return response()->json('Successfully added items to cart.', 200);
        }
    }

    /**
     * Add multiple items to a custom order cart.
     *
     * @return Response
     */
    public function createCustomCartlines(Request $request)
    {
        $inputs = $request->all();
        if (!empty($inputs)) {
            $cartType = $inputs[0]['cart_type'];
        } else {
            return response()->json('Nothing to Add', 422);
        }
        $cartType = $inputs[0]['cart_type'];
        $addStatus = $this->customCartService->processNewCartlines($inputs, $cartType);
        if ($addStatus !== true) {
            return response()->json($addStatus, 422);
        } else {
            return response()->json('Successfully added items to cart.', 200);
        }
    }

    /**
     * Update a cartline to a new quantity.
     *
     * @return Response
     */
    public function patchCartline(Request $request)
    {
        $this->validate($request, [
            'quantity' => 'integer|min:1',
            'item_id' => 'integer'
        ]);
        $data = $request->all();
        if (!isset($data['cart_type'])) {
            $data['cart_type'] = 'cart';
        }
        $cart = $this->cartRepo->patch($data['quantity'], $data['item_id'], $data['cart_type']);
        if (!$cart) {
            return response()->json(['message' => 'Could not update the cart.'], 400);
        }

        session()->put('cart', $cart);
        return response()->json($cart, 200);
    }

    /**
     * Delete a cartline.
     *
     * @param Int $id
     * @return Response
     */
    public function deleteCartline($id)
    {
        if ($cart = $this->cartRepo->deleteCartline($id)) {
            $cart = $this->cartRepo->updateTotals($cart);
            session()->put('cart', $cart);
            return response()->json($cart, 200);
        }

        return response()->json(['Failed to delete cartline.'], 400);
    }

    /**
     * Updates quantity of a bundle in the cart.
     *
     * @return Response
     */
    public function patchBundle()
    {
        $data = request()->all();
        $cart = $this->cartRepo->show();
        if ($cart = $this->cartRepo->patchBundle($data['bundle_id'], $cart, $data['quantity'])) {
            $cart = $this->cartRepo->updateTotals($cart);
            session()->put('cart', $cart);
            return response()->json(['cart' => $cart, 'message' => 'Added Bundle to Cart', 'error' => false], HTTP_SUCCESS);
        }
        return response()->json(['error' => true, 'message' => 'Failed to Add Bundle to Cart'], 400);
    }

    /**
     * Adds a bundle to the cart.
     *
     * @return Response
     */
    public function putBundle()
    {
        $data = request()->all();
        $cart = $this->cartRepo->show();
        if ($cart = $this->cartRepo->putBundle($data['bundle_id'], $cart, $data['quantity'])) {
            $cart = $this->cartRepo->updateTotals($cart);
            session()->put('cart', $cart);
            return response()->json(['cart' => $cart, 'message' => 'Updated Bundle on the Cart', 'error' => false], HTTP_SUCCESS);
        }
        return response()->json(['error' => true, 'message' => 'Failed to update Bundle on the Cart'], 400);
    }

    private function getStoreOwner()
    {
        // find the subdomain
        $pieces = explode('.', request()->getHost());
        $subdomain = $pieces[0];
        if ($subdomain === strstr(env('APP_URL'), '.', true) || $subdomain === strstr(env('STORE_DOMAIN'), '.', true)) {
            $storeOwner = User::where('id', '=', 1)->first();
        }
        $storeOwner = User::where('public_id', $subdomain)->first();

        if (empty($storeOwner)) {
            abort(500);
        }
        return $storeOwner;
    }
}
