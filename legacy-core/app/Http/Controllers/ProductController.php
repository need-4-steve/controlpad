<?php namespace App\Http\Controllers;

use Log;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\RoleRepository;
use App\Services\Store\Store;
use App\Services\Store\RepStore;
use App\Repositories\Eloquent\AuthRepository;
use App\Models\LiveVideo;
use App\Services\LiveVideo\FacebookLiveVideo;
use App\Services\UserStatus\UserStatusService;

class ProductController extends Controller
{
    protected $productRepository;
    protected $rolesRepo;
    protected $facebookLiveVideo;

    public function __construct(
        FacebookLiveVideo $facebookLiveVideo,
        ProductRepository $productRepository,
        RoleRepository $rolesRepo,
        AuthRepository $authRepo
    ) {
        $this->productRepository = $productRepository;
        $this->rolesRepo = $rolesRepo;
        $this->settingsService = app('globalSettings');
        $this->authRepo = $authRepo;
    }
    /**
     * Display a listing of products
     *
     * @return Response
     */
    public function index($type = 'Product')
    {
        return view('product.index', compact('type'));
    }

    /**
     * Display a listing of products by category
     *
     * @return Response
     */
    public function category($category)
    {
        $category = str_replace('-', ' ', $category);
        $api_url = 'products/category/' . $category;
        return view('product.index', compact('api_url'));
    }

    /**
     * Display a listing of public products
     *
     * @return Response
     */
    public function storeFront()
    {
        $request = request()->all();
        $store_owner = session()->get('store_owner');

        if ($store_owner && $this->settingsService->getGlobal('replicated_site', 'show')) {
            $userStatusService = new UserStatusService;
            // Redirect if rep can't sell
            if (!$userStatusService->checkPermission($store_owner, 'sell')) {
                return redirect($userStatusService->getSellRedirectUrl());
            }
            if ($this->settingsService->getGlobal('events_as_replicated_site', 'show')
            ) {
                return redirect('store/events');
            }
            $store = new RepStore($request, $store_owner, $this->productRepository);
        } elseif (! $store_owner && $this->settingsService->getGlobal('use_built_in_store', 'show')) {
            $store = new Store($request, config('site.apex_user_id'), $this->productRepository);
        } else {
            return abort(404);
        }

        return view('product.storefront', compact('category', 'store', 'store_owner'));
    }

    /**
     * undocumented function summary
     *
     * Undocumented function long description
     *
     * @param type var Description
     * @return return type
     */
    public function getVideo()
    {
        // LIVE VIDEOS
        $liveVideoCache = cache('is_live_streaming.'.session('store_owner.public_id'));
        if (isset($liveVideoCache['stream']['personal']) && $liveVideoCache['stream']['personal']) {
            $inventory = LiveVideo::with('liveVideoProduct.media')
                ->where('id', $liveVideoCache['record']['id'])
                ->first();
        } else {
            $inventory = LiveVideo::with([
                'liveVideoInventory.inventory.item.prices',
                'liveVideoInventory.inventory.item.product.media'
                ])->where('id', $liveVideoCache['record']['id'])->first();
        }
        return [
            'stream'   => collect($liveVideoCache['stream']),
            'video'    => collect($liveVideoCache['record']),
            'inventory' => $inventory,
        ];
    }

    /**
     * Show the form for creating a new product
     *
     * @return Response
     */
    public function create()
    {
        $verbiage = [
            'primary' => 'Create New Product',
            'secondary' => 'Create New Product',
        ];

        if (auth()->user()->hasRole(['Admin', 'Superadmin']) or
            auth()->user()->hasSellerType(['Reseller']) and
            $this->settingsService->getGlobal('reseller_create_product', 'show') or
            auth()->user()->hasSellerType(['Affiliate']) and
            $this->settingsService->getGlobal('affiliate_create_product', 'show')
        ) {
            if ($this->settingsService->getGlobal('new_product_create', 'show')) {
                return view('product.product_form_beta');
            }
            return view('product.product_form');
        }
        return redirect('/dashboard');
    }

    /**
     * Display the specified product.
     *
     * @param  int  $id
     * @return Response
     */

    public function show($id)
    {
        // get product
        $product = $this->productRepository->find($id, ['media', 'recommended', 'tags', 'items']);
        return view('product.form', compact('product', 'organizer'));
    }

    /**
    * Display the public view for the specified product.
    *
    * @param  int  $id
    * @return Response
    */
    public function publicShow($slug)
    {
        $store_owner = session()->get('store_owner');

        if ($store_owner && !$this->settingsService->getGlobal('replicated_site', 'show')) {
            return abort(404);
        } elseif (!$this->settingsService->getGlobal('use_built_in_store', 'show') && !$store_owner) {
            return abort(404);
        }

        $params = request()->all();
        // get product
        if ($store_owner
            && is_null(request()->input('corporate'))
            && !$store_owner->hasSellerType('Affiliate')
        ) {
            $storeOwnerId = $store_owner->id;
            $userStatusService = new UserStatusService;
            // Redirect if rep can't sell
            if (!$userStatusService->checkPermission($store_owner, 'sell')) {
                return redirect($userStatusService->getSellRedirectUrl());
            }
            if (\App\Models\UserSetting::where('user_id', $storeOwnerId)->first()->hide_products) {
                return redirect('/store');
            }
            $store = new RepStore($params, $store_owner, null, null, false);
        } else {
            $storeOwnerId = config('site.apex_user_id');
            $store = new Store($params, $storeOwnerId, null, null, false);
            if ($store_owner) { // This is for affiliates, this all needs refactored anyway
                $store->rep = $store_owner;
            }
        }

        $client = new \GuzzleHttp\Client;
        try {
            $response = $client->get(
                env('INVENTORY_API_URL', 'https://inventory.controlpadapi.com/api/v0').'/products/slug/'.$slug,
                [
                    'headers' => [
                        'Origin' => request()->url()
                    ],
                    'json' => [
                        'expands' => [
                            'variants',
                            'product_images',
                            'variant_images',
                        ],
                        'user_id' => $storeOwnerId,
                        'available' => true
                    ]
                ]
            );
            $product = json_decode($response->getBody(), false);
        } catch (\Exception $e) {
            if ($e->getCode() === 404) {
                return redirect()->back()->with('message_danger', 'Product is out of inventory or seller has made it unavailable.');
            }
            Log::error($e, ['fingerprint' => 'Error while trying to find product.']);
            return redirect()->back()->with('message_danger', 'Error while trying to find product.');
        }

        return view(
            'product.public_show',
            compact('product', 'store_owner', 'store')
        );
    }

    /**
     * Show the form for editing the specified product.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        if ($this->settingsService->getGlobal('new_product_create', 'show')) {
            return view('product.product_form_beta');
        }
        // get product with options and tags
        $product = $this->productRepository->find($id, [
            'items.premiumPrice',
            'items.msrp',
            'items.wholesalePrice',
            'tags',
            'media',
            'category.parent',
            'roles']);
        if ($product->user_id !== $this->authRepo->getOwnerId()) {
            return redirect('products')->with('message', 'You are not authorized to edit product ' . $id);
        }
        if (!$product) {
            return redirect('products')->with('message', 'Product not found with ID ' . $id);
        };

        if ($this->settingsService->getGlobal('reseller_create_product', 'show') && !$this->authRepo->isOwnerAdmin()) {
            $role = [
                $this->rolesRepo->find(3)
            ];
        } else {
            $role = $this->rolesRepo->all();
        }

        $product->checkedRoles = $this->rolesRepo
                                    ->findCheckedRoles(
                                        $role,
                                        $product->roles
                                    );

        // verbiage for form
        $verbiage = [
            'primary' => 'Edit Product',
            'secondary' => 'Update'
        ];
        if (auth()->user()->hasRole(['Admin', 'Superadmin']) or
            auth()->user()->hasSellerType(['Reseller']) and
            $this->settingsService->getGlobal('reseller_create_product', 'show') or
            auth()->user()->hasSellerType(['Affiliate']) and
            $this->settingsService->getGlobal('affiliate_create_product', 'show')
        ) {
            return view(
                'product.product_edit',
                compact(
                    'product',
                    'verbiage'
                )
            );
        }
        return redirect('/dashboard');
    }
}
