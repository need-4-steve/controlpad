<?php namespace App\Http\Controllers;

use Auth;
use Cache;
use Config;
use Input;
use Redirect;
use Session;
use Validator;
use App\Models\Cart;
use App\Models\Cartline;
use App\Models\User;
use App\Models\Promotion;
use App\Models\Product;
use App\Models\Item;
use App\Repositories\Eloquent\CartRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Services\Store\RepStore;
use App\Services\Store\Store;
use App\Models\Inventory;

class CartController extends Controller
{
    protected $cartRepo;
    /**
     * Create a new controller instance.
     *
     * @param  CartRepository  $cart
     * @return void
     */
    public function __construct(CartRepository $cartRepo, ProductRepository $productRepository)
    {
        $this->cartRepo = $cartRepo;
        $this->productRepository = $productRepository;
        $this->settingsService = app('globalSettings');
    }

    public function index($user_id = null)
    {
        $layout = 'store';
        $repShopping = false;

        // extend boss sub-layout if shopping as a rep
        if (auth()->check() && auth()->user()->hasRole('Rep')) {
            $layout = 'boss';
            $repShopping = true;
        }
        $request = request()->all();
        $store_owner = session()->get('store_owner');

        if ($store_owner && !$this->settingsService->getGlobal('replicated_site', 'show') ||
            !$store_owner && !$this->settingsService->getGlobal('use_built_in_store', 'show')) {
                return abort(404);
        }
        $store = (object)['rep' => $store_owner];
        $autoshipUrl = env('AUTOSHIP_API_URL', 'https://autoship.controlpadapi.com/api/v0');
        $view = view('cart.show', compact('user_id', 'layout', 'repShopping', 'store', 'autoshipUrl'));
        if (request()->has('pid')) {
            if (!isset($store_owner)) {
                $store_owner = User::select('pid')->where('id', config('site.apex_user_id'))->first();
            }
            $cart = Cart::with('lines')->where('pid', $request['pid'])->first();
            if (!isset($cart)) {
                $view->with('message_danger', 'Cart not found or has been expired');
                session()->forget('cart');
            } elseif ($cart->seller_pid !== $store_owner->pid) {
                $view->with('message_danger', 'Mismatch of cart and seller');
                session()->forget('cart');
            } else {
                session()->put('cart', $cart);
            }
        }
        return $view;
    }

    public function show()
    {
        $request = request();
        if ($request->has('cart_pid')) {
            $cart = Cart::with('lines')->where('pid', $request['cart_pid'])->first();
            if ($cart !== null) {
                $storeOwner = User::where('pid', $cart->inventory_user_pid)->first();
                $store = (object)['rep' => $storeOwner];
                session()->put('store_owner', $storeOwner);
                session()->put('cart', $cart);
            }
        } else {
            $cart = null;
        }

        $routePrefix = env('APP_ENV') == 'production' ? 'https://' : 'http://';
        if ($cart !== null && $cart->type === 'affiliate') {
            $returnRoute = $routePrefix . (!empty(env('AFFILIATE_STORE_DOMAIN')) ? env('AFFILIATE_STORE_DOMAIN') : env('STORE_DOMAIN'));
        } elseif (isset($storeOwner) && $storeOwner->hasRole(['Rep']) && $storeOwner->seller_type_id === 2) {
            if (!empty(env('REP_STORE_DOMAIN'))) {
                $storeDomain = env('REP_STORE_DOMAIN');
            } else {
                $storeDomain = env('STORE_DOMAIN');
            }
            $returnRoute = $routePrefix . $storeOwner->public_id . '.' . str_replace('myoffice.', '', $storeDomain);
        } else {
            $returnRoute = $routePrefix . env('STORE_DOMAIN');
        }

        $layout = 'public-cart';
        $repShopping = false;
        $autoshipUrl = env('AUTOSHIP_API_URL', 'https://autoship.controlpadapi.com/api/v0');
        return $view = view('cart.show', compact('layout', 'repShopping', 'returnRoute', 'autoshipUrl'));
    }
}
