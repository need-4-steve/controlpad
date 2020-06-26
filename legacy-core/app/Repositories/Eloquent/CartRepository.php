<?php namespace App\Repositories\Eloquent;

use Auth;
use Config;
use Input;
use Session;
use Validator;
use DB;
use Log;
use App\Models\Bundle;
use App\Models\Cart;
use App\Models\Cartline;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\User;
use App\Models\ShippingRate;
use App\Repositories\Eloquent\AuthRepository;
use App\Repositories\Eloquent\CouponAppliedRepository;
use App\Repositories\Eloquent\InventoryRepository;
use App\Repositories\Eloquent\MediaRepository;
use App\Repositories\Eloquent\ShippingRateRepository;
use App\Repositories\Eloquent\Traits\CommonCrudTrait;
use App\Repositories\Eloquent\ProductRepository;
use App\Services\Shipping\ShippingService;
use App\Services\Tax\TaxService;
use CPCommon\Pid\Pid;

class CartRepository
{
    use CommonCrudTrait;

    protected $inventoryRepo;
    protected $shippingService;
    protected $couponAppliedRepo;
    protected $settings;

    public function __construct(
        InventoryRepository $inventoryRepo,
        ShippingRateRepository $shippingRateRepo,
        CouponAppliedRepository $couponAppliedRepo,
        ProductRepository $productRepo
    ) {
        $this->inventoryRepo = $inventoryRepo;
        $this->shippingRateRepo = $shippingRateRepo;
        $this->couponAppliedRepo = $couponAppliedRepo;
        $this->settings = app('globalSettings');
        $this->productRepo = $productRepo;
        $this->authRepo = new AuthRepository;
    }

    /**
     * Delete a cartline.
     */
    public function deleteCartline($id)
    {
        $cartUid = $this->show()->uid;
        $cartline = Cartline::find($id);

        if (empty($cartline)) {
            Log::error('Tried to delete a nonexistant cart in CartRepository removeCartline() method');
            return false;
        }
        $cartline->delete();
        $cart = $this->find($cartUid);
        return $cart;
    }

    /**
     * Delete a cart.
     */
    public function delete($uid = null)
    {
        $cart = Cart::with('lines', 'shipping')
                        ->where('uid', $uid)
                        ->first();

        if (empty($cart)) {
            Log::error('Tried to delete a nonexistant cart in CartRepository delete() method');
            return false;
        }

        // if cartlines exist, delete each cartline
        $lineIds = $cart->lines()->pluck('id')->toArray();
        Cartline::whereIn('id', $lineIds)->delete();

        // delete the cart
        $cart->delete();
        return true;
    }

    /**
     * Update a cartline to a new quantity.
     */
    public function patch($quantity, $item_id, $cart_type = 'cart')
    {
        $cart = $this->show(null, $cart_type);
        // find the cartline correlating to the item
        $cartline = $cart->lines()->where('item_id', $item_id)->first();
        if (!$cartline) {
            Log::error('Tried to patch a nonexistant cartline in CartRepository patch() method');
            return false;
        }

        // update the cartlilne quantity to the new quantity
        $cartline->quantity = $quantity;
        $cartline->save();

        // update totals to the new quantity
        $cart = $this->find($cart->uid);
        $cart = $this->updateTotals($cart, null, $cart_type);
        return $cart;
    }

    /**
     * Add item to cart.
     */
    public function addItem(array $items, Cart $cart, $itemIds, $type = 'cash')
    {
        Cartline::where('cart_id', $cart->id)->whereIn('item_id', $itemIds)->delete();
        Cartline::insert($items);
        $this->updateTotals($cart, null, $type);
        return true;
    }

    /**
     * Get cart or create a new one.
     */
    public function show($uid = null, $type = 'cart')
    {
        if ($cart = session()->get($type)) {
            return $cart;
        }
        $cartData = [];
        if (auth()->id()) {
            $cartData['user_id'] = auth()->id();
        }

        // Save a new type for use in order api. Shouldn't affect session type. Added in checkout api phase 1
        $cartData['pid'] = Pid::create();
        if (isset(auth()->user()->pid)) {
            $authUserPid = auth()->user()->pid;
            $isAdmin = auth()->user()->hasRole(['Superadmin', 'Admin']);
        } else {
            $authUserPid = null;
            $isAdmin = false;
        }
        $cartData['buyer_pid'] = $authUserPid;
        $companyPid = User::select('pid')
            ->where('id', config('site.apex_user_id'))->first()->pid;

        switch ($type) {
            case 'custom_personal':
                $cartData['type'] = 'custom-retail';
                $cartData['seller_pid'] = ($isAdmin ? $companyPid : $authUserPid);
                $cartData['inventory_user_pid'] = $cartData['seller_pid'];
                break;
            case 'wholesale':
                $cartData['seller_pid'] = $companyPid;
                $cartData['type'] = 'wholesale';
                break;
            case 'retail':
                if (session()->get('store_owner.pid') == null) {
                    $cartData['seller_pid'] = $companyPid;
                } else {
                    $cartData['seller_pid'] = session()->get('store_owner.pid'); // Only works for replicated sites
                }
                $cartData['type'] = 'retail';
                break;
            case 'cart':
                $storeOwner = session()->get('store_owner'); // Only works for replicated sites
                $cartData['buyer_pid'] = null; // We can't have a buyer on the store front because the auth is different in checkout
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
                break;
            case 'custom_corp':
                if ($isAdmin) {
                    $cartData['type'] = 'custom-corp';
                    $cartData['seller_pid'] = $companyPid;
                } else {
                    // Rep selling company product
                    $cartData['type'] = 'affiliate';
                    $cartData['seller_pid'] = $authUserPid;
                }
                $cartData['inventory_user_pid'] = $companyPid;
                break;
            default:
                Log::warning('Unexpected cart type selected during show(): ' . $type);
                $cartData['type'] = $type;
                // Can't be sure of inventory user if we don't know the type
        }
        // End prefill checkout api data

        $cart = Cart::create($cartData);
        $cart->save();
        session()->put($type, $cart);
        return $cart;
    }

    /**
     * Find a cart by uid.
     */
    public function find($uid)
    {
        return Cart::with('lines', 'shipping')
                    ->where('uid', $uid)
                    ->first();
    }

    // TODO: reorganize this function, clean up orders in general
    /**
     * Update the total prices of a cart.
     * Adds up cartlines, coupons,
     * shipping and tax.
     */
    public function updateTotals(Cart $cart, $taxRateRequest = null, $type = 'cart', $calcShipping = true)
    {
        $cart->load(
            'shipping',
            'bundles.items.product.media',
            'bundles.wholesalePrice',
            'bundles.media',
            'lines.item.product.type',
            'lines.item.product.media',
            'lines.item.variant.media',
            'coupons'
        );
        // if we have no products, return cart
        if (empty($cart->lines) and empty($cart->bundles)) {
            return $cart;
        }

        // separate inventory by source
        $store_owner = $this->authRepo->getStoreOwner();
        $store_owner_id = $store_owner->id;
        if ($type === 'custom_personal') {
            $store_owner = $this->authRepo->getOwner();
            $store_owner_id = $store_owner->id;
        }

        $cart->subtotal_price = 0;
        $cart->total_discount = 0;
        $cart->total_tax = 0;
        $cart->total_shipping = 0;
        $cart->total_price = 0;
        $cart->user_id = 0;
        $shippable_subtotal = 0;
        $coupon = null;

        $personal = false;
        if (isset($taxRateRequest['order_type']) && $taxRateRequest['order_type'] === 'personal') {
            $personal = true;
        }
        // set custom discount and shipping
        if ($type === 'custom_personal' && $taxRateRequest !== null && !$personal) {
            $cart->total_discount = $taxRateRequest['discountRate'];
            $cart->subtotal_price -= $cart->total_discount;
            $cart->total_shipping = $taxRateRequest['shippingCost'];
        }
        $taxExempt = false;
        if (isset($taxRateRequest['tax_exempt']) && $taxRateRequest['tax_exempt']) {
            $taxExempt = true;
        }

        // add up items
        $liveVideoSale = cache('is_live_streaming.'.session('store_owner.public_id'));
        if ($liveVideoSale && session('store_owner') && session('store_owner')->seller_type_id === 2) { // for when a live facebook sale is occurring
            $saleItems = \App\Models\LiveVideoInventory::where('user_id', $store_owner_id)
                ->where('live_video_id', $liveVideoSale['record']['id'])->get();
            foreach ($cart->lines as $line) {
                $saleItem = $saleItems->where('item_id', $line->item_id)->first();
                if ($saleItem) {
                    $line->discount = $saleItem->discount_amount;
                    $line->discount_type_id = 1; // facebook live sale
                    $line->save();
                    $cart->subtotal_price += ($line->price - $saleItem->discount_amount) * $line->quantity;
                    $shippable_subtotal += ($line->price - $saleItem->discount_amount) * $line->quantity;
                } else {
                    $cart->subtotal_price += $line->price * $line->quantity;
                    $shippable_subtotal += $line->price * $line->quantity;
                }
            }
        } else { // without line discounts
            if ($personal) {
                $cart->load('lines.item.wholesalePrice', 'lines.item.product');
            }
            foreach ($cart->lines as $line) {
                if ($personal) {
                    $line->price = $line->item->wholesalePrice->price;
                }
                $cart->subtotal_price += $line->price * $line->quantity;
                $shippable_subtotal += $line->price * $line->quantity;
            }
        }


        // add up bundles
        foreach ($cart->bundles as $bundle) {
            $cart->subtotal_price += $bundle->wholesalePrice->price * $bundle->pivot->quantity;
            if ($bundle->type_id !== 2) { // Type Id 2 = fullfilled by corporate bundle. Shipping is not payed on it.
                $shippable_subtotal += $bundle->wholesalePrice->price * $bundle->pivot->quantity;
            }
        }

        // apply coupons
        if (isset($cart->coupons) && count($cart->coupons) > 0) {
            $cart = $this->couponAppliedRepo->apply($cart, $cart->coupons()->first()->owner_id);
            $coupon = $cart->coupons[0];
        };

        // grab shipping cost
        if ($shippable_subtotal > 0 && $type !== 'custom_personal' && $calcShipping) {
            if ($type === 'custom_personal' && $store_owner->hasRole(['Rep'])) {
                $shipping = new ShippingRate(['amount' => 0]);
            } elseif (session()->has('store_owner') && session()->get('store_owner.seller_type_id') !== 2 || $store_owner->hasRole('Admin', 'Superadmin')) {
                $shipping = $this->shippingRateRepo
                            ->findPriceForUser(config('site.apex_user_id'), $shippable_subtotal);
            } else {
                $shippingRateType = null;
                if ($type === 'custom_corp') {
                    $shippingRateType = 'retail';
                } elseif ($cart->type === 'wholesale') {
                    $shippingRateType = 'wholesale';
                }
                $shipping = $this->shippingRateRepo->findPriceForUser($store_owner_id, $shippable_subtotal, $shippingRateType);
            }
            if (isset($shipping->amount) && $shipping->amount !== null) {
                $cart->total_shipping += $shipping->amount;
                // calculate (if any) premium shipping costs
                $cart->total_shipping += $this->calculatePremiumShippingCost($cart->lines);
            } else {
                return ['error' => 'Shipping rates have not been set, contact '
                        . $store_owner->public_id . ' to resolve this issue.'];
            }
        }
        if (isset($shipping) && !$shipping) {
            return ['error' => 'Shipping rate could not be found.'];
        }

        if (auth()->check()
            && auth()->user()->hasRole(['Rep'])
            && $this->settings->getGlobal('tax_exempt_wholesale', 'show')
            && $store_owner->hasRole(['Superadmin','Admin'])
            && $type !== 'custom_corp') {
            $cart->total_tax = 0;
        } elseif ($this->settings->getGlobal('tax_calculation', 'show') &&
            $taxRateRequest !== null &&
            !$taxExempt &&
            (isset($taxRateRequest['addresses']['shipping']['zip']) || isset($taxRateRequest['shippingAddress']['zip'])) &&
            (count($cart->lines) > 0 ||
            count($cart->bundles) > 0)) {
            if ($store_owner->hasRole(['Rep']) && $store_owner->hasSellerType(['Affiliate']) || $type === 'custom_corp' || $store_owner->hasRole(['Superadmin','Admin'])) {
                $sendingAddress = $this->authRepo->getCorporateBusinessAddress();
            } else {
                $sendingAddress = $store_owner->businessAddress;
            }
            if (isset($taxRateRequest['user']['id'])) {
                $cart->user_id = $taxRateRequest['user']['id'];
            } elseif (isset($taxRateRequest['user']['email'])) {
                $user = User::select('id', 'email', 'first_name', 'last_name')
                        ->where('email', $taxRateRequest['user']['email'])->first();
                if (isset($user)) {
                    $cart->user_id = $user->id;
                }
            }
            // Remove this when angular is finally removed!!! Needs to remain because of angular caching.
            if (isset($taxRateRequest['addresses']['shipping'])) {
                $shipping = $taxRateRequest['addresses']['shipping'];
            } elseif ($taxRateRequest['shippingAddress']) {
                $shipping = $taxRateRequest['shippingAddress'];
            } else {
                return ['error' => 'Shipping Address not set'];
            }
            if (isset($taxRateRequest['addresses']['billing'])) {
                $billing = $taxRateRequest['addresses']['billing'];
            } elseif ($taxRateRequest['billingAddress']) {
                $billing = $taxRateRequest['billingAddress'];
            } else {
                return ['error' => 'Billing Address not set'];
            }
            $taxResponse = (new TaxService())->createCartTaxInvoice(
                $billing,
                $shipping,
                $sendingAddress,
                $cart,
                'sale',
                $this->authRepo->getStoreOwner()->pid,
                false
            );
            // check for error
            if (isset($taxResponse->error)) {
                return ['error' => $taxResponse->error];
            }
            $cart->total_tax = $taxResponse->tax;
            $cart->tax_invoice_pid = $taxResponse->pid;
        }
        $cart->subtotal_price = round($cart->subtotal_price, 2);
        $cart->total_price = round($cart->subtotal_price + $cart->total_tax + $cart->total_shipping, 2);
        if ($personal) {
            $cart->total_discount = $cart->subtotal_price;
            $cart->subtotal_price = 0;
            $cart->total_price = $cart->total_tax;
        }
        $cart->save();

        return $cart;
    }

    /**
     * calculates premium shipping cost
     *
     * @param array $items
     * @return number
     */
    private function calculatePremiumShippingCost($lines)
    {
        $premiumShippingCost = 0;
        foreach ($lines as $line) {
            if ($line->item->premium_shipping_cost !== null) {
                $premiumShippingCost += ($line->item->premium_shipping_cost * $line->quantity);
            }
        }
        return $premiumShippingCost;
    }

    /**
     * Updates a bundle quantity in the cart.
     */
    public function patchBundle($bundle_id, Cart $cart, $quantity)
    {
        if ($quantity > 0) {
            $bundle_ids = $cart->bundles()->pluck('bundle_id')->toArray();
            $bundle_ids = array_fill_keys($bundle_ids, []);
            $bundle_ids[$bundle_id] = [
                'quantity' => $quantity
            ];
            $cart->bundles()->sync($bundle_ids);
            // New checkout api cartline handling
            Cartline::where('cart_id', '=', $cart->id)->where('bundle_id', '=', $bundle_id)->update(['quantity' => $quantity]);
        } else {
            $cart->bundles()->detach($bundle_id);
            // New checkout api cartline handling
            Cartline::where('cart_id', '=', $cart->id)->where('bundle_id', '=', $bundle_id)->delete();
        }
        $cart = $this->find($cart->uid, ['lines', 'bundles']);
        return $cart;
    }

    /**
     * Adds a bundle to the cart.
     */
    public function putBundle($bundle_id, Cart $cart, $quantity)
    {
        if ($quantity > 0) {
            if ($cartBundle = $cart->bundles()->where('bundle_id', $bundle_id)->first()) {
                $cartBundle->pivot->quantity += $quantity;
                $cartBundle->pivot->save();
                // New checkout api cartline handling
                Cartline::where('cart_id', '=', $cart->id)->where('bundle_id', '=', $bundle_id)->update(['quantity' => $quantity]);
            } else {
                $this->addBundleAsCartLine($cart, $bundle_id, $quantity); // Used for new checkout api
                $cart->bundles()->attach([
                    $bundle_id => [
                        'quantity' => $quantity
                    ],
                ]);
            }
        }
        return $cart;
    }

    /**
     * Empty the cart.
     */
    public function emptyCart($cart_id)
    {
        return Cartline::where('cart_id', $cart_id)->delete();
    }

    // New checkout api will add bundle as a cartline with items serialized. Filter lines with item_id === null for now.
    private function addBundleAsCartLine($cart, $bundleId, $quantity)
    {
        $ownerId = config('site.apex_user_id');
        $owner = User::select('pid')->where('id', '=', $ownerId)->first();
        if (isset($owner->pid)) {
            $ownerPid = $owner->pid;
        } else {
            $ownerPid = null;
        }
        $bundle = Bundle::select('name', 'tax_class', 'prices.price as price')
            ->join('prices', 'prices.priceable_id', '=', 'bundles.id')
            ->where('prices.priceable_type', '=', 'App\Models\Bundle')
            ->where('prices.price_type_id', '=', 1)
            ->where('bundles.id', $bundleId)->first();

        $items = Item::select(
            'items.id as id',
            'inventories.id as inventory_id',
            'items.product_id',
            'products.name as product_name',
            'items.variant_id',
            'items.size as option',
            'items.manufacturer_sku as sku',
            'variants.name as variant_name',
            'variants.option_label as option_label',
            'items.premium_shipping_cost',
            'items.weight',
            'products.variant_label',
            'bundle_item.quantity as quantity',
            'products.variant_label'
        )
            ->join('bundle_item', 'bundle_item.item_id', '=', 'items.id')
            ->join('variants', 'variants.id', '=', 'items.variant_id')
            ->join('products', 'products.id', '=', 'items.product_id')
            ->leftJoin('inventories', 'inventories.item_id', '=', 'items.id')
            ->where('bundle_item.bundle_id', '=', $bundleId)
            ->where('inventories.user_id', '=', $ownerId)
            ->where('inventories.owner_id', '=', $ownerId)
            ->get();

        // Append media image url to items
        $media = (new MediaRepository($this->authRepo))->getMediaForItems($items);
        foreach ($items as $key => $item) {
            if (isset($media['variant_media'][$item->variant_id])) {
                $item->img_url = $media['variant_media'][$item->variant_id]['url'];
            } elseif (isset($media['product_media'][$item->product_id])) {
                $item->img_url = $media['product_media'][$item->product_id]['url'];
            } else {
                $item->img_url = null;
            }
            unset($item->product_id);
            unset($item->variant_id);
        }

        $cartline = Cartline::create([
            'pid' => \CPCommon\Pid\Pid::create(),
            'cart_id' => $cart->id,
            'item_id' => null,
            'bundle_id' => $bundleId,
            'bundle_name' => $bundle->name,
            'quantity' => $quantity,
            'price' => $bundle->price,
            'inventory_owner_id' => $ownerId,
            'inventory_owner_pid' => $ownerPid,
            'event_id' => null,
            'tax_class' => $bundle->tax_class,
            'items' => $items
        ]);
    }
}
