<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Models\ProductType;
use App\Models\Item;
use App\Models\Bundle;
use App\Models\Role;
use App\Models\Inventory;
use App\Models\Media;
use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Event;
use App\Repositories\Contracts\ProductRepositoryContract;
use App\Repositories\Eloquent\Traits\CommonCrudTrait;
use App\Repositories\Eloquent\AuthRepository;
use App\Repositories\Contracts\ItemRepositoryContract;
use App\Repositories\Eloquent\ItemRepository;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Eloquent\InventoryRepository;
use DB;

class ProductRepository implements ProductRepositoryContract
{
    use CommonCrudTrait;

    public function __construct(
        ItemRepository $itemRepo,
        CategoryRepository $categoryRepo,
        AuthRepository $authRepo,
        InventoryRepository $inventoryRepo
    ) {
        $this->itemRepo = $itemRepo;
        $this->categoryRepo = $categoryRepo;
        $this->settings = app('globalSettings');
        $this->authRepo = $authRepo;
        $this->inventoryRepo = $inventoryRepo;
    }

    /**
     * return an index of all products
     *
     * @param array
     * @return object
     */
    public function getCorporateProductsByRole($visibility_id, $input, $affiliate = false, $eventId = null)
    {
        $price = 2; // Retail price
        if ($visibility_id === 5) { // Role of Rep
            $price = 1; // Wholesale price
        }
        if ($eventId) {
            $event = Event::where('id', $eventId)->first();

            if ($event && $event->product_ids !== null) {
                $productIds = json_decode($event->product_ids);
            } else {
                $eventId = null;
            }
        }
        $products = Product::select('products.id', 'products.name', 'products.min', 'products.max', 'prices.price as price', 'products.slug', DB::raw('SUM(inventories.quantity_available) as quantity_available'))
                    ->join('product_visibility', 'product_visibility.product_id', '=', 'products.id')
                    ->join('visibilities', 'visibilities.id', '=', 'product_visibility.visibility_id')
                    ->join('items', 'items.product_id', '=', 'products.id')
                    ->join('inventories', 'inventories.item_id', '=', 'items.id')
                    ->join('prices', 'prices.priceable_id', 'items.id');
        if ($eventId) {
            $products = $products->whereIn('products.id', $productIds);
        }
        if ($input['category'] !== null && $input['category'] !== '') {
            $products = $products->join('product_category', 'product_category.product_id', '=', 'products.id')
                                ->join('categories', 'categories.id', '=', 'product_category.category_id');
        }
        $products = $products->where('visibilities.id', $visibility_id)
                    ->where('inventories.user_id', '=', config('site.apex_user_id'))
                    ->whereNull('inventories.deleted_at')
                    ->whereNull('inventories.disabled_at')
                    ->where(function ($query) {
                        $query->where('inventories.expires_at', '>', date("Y-m-d"))
                              ->orWhereNull('inventories.expires_at');
                    })
                    ->where('prices.priceable_type', 'App\Models\Item')
                    ->where('prices.price_type_id', $price);
        if ($input['category'] !== null && $input['category'] !== '') {
            $products = $products->where('categories.id', $input['category']);
        }
        if (isset($input['searchTerm']) && $input['searchTerm'] !== '') {
            $products->search($input['searchTerm']);
        }

        $products->with('media');
        $products = $products->orderBy($input['sortBy'], $input['order'])
                            ->groupBy('items.product_id')
                            ->paginate($input['limit']);
        $products->totalAvailable = $products->total();

        return $products;
    }

    /**
     * return an index of all products
     *
     * @param array
     * @return object
     */
    public function index(
        array $request = [
            'category' => 'all',
            'searchTerm' => '',
            'limit' => 10,
            'order' => 'DESC',
            'column' => 'updated_at'
        ]
    ) {
        $products = Product::select(
            'products.id',
            'products.name',
            'products.updated_at'
        )
        ->with('category');

        $products = $products->where('products.user_id', $this->authRepo->getOwnerId())
            ->where(function ($query) use ($request) {
                $query->where('products.name', 'LIKE', '%'.$request['searchTerm'].'%')
                    ->orWhere('products.updated_at', 'LIKE', '%'.$request['searchTerm'].'%');
            });

        if ($request['category'] !== 'all') {
            $products = $products->where('category_id', $request['category']);
        }
        if (!isset($request['per_page'])  || !$request['per_page']) {
            $request['per_page'] = $request['limit'];
        }
        if ($request['per_page']) {
            $products = $products->orderBy($request['column'], $request['order'])
              ->paginate($request['per_page']);
              $products->load('media');
        }
        return $products;
    }
    /**
     * get products by inventory and category, and sort them
     *
     * @param int user_id, array queryStrs
     * @return object|Product
     */
    public function getByInventoryAndCategory(int $user_id, $queryStrs, $eventId = null)
    {
        if ($eventId) {
            $event = Event::where('id', $eventId)->first();

            if ($event && $event->product_ids !== null) {
                $productIds = json_decode($event->product_ids);
            } else {
                $eventId = null;
            }
        }

        $products = Product::select('*', 'products.id as id')
        ->search($queryStrs['searchTerm'])
        ->with(['items.inventory' => function ($query) use ($user_id) {
             $query->where('user_id', $user_id);
        }])
        ->join('items', function ($join) {
            $join->on('items.product_id', '=', 'products.id');
        })
        ->join('inventories', function ($join) use ($user_id) {
            $join->on('inventories.item_id', '=', 'items.id')
            ->where('inventories.user_id', $user_id)
            ->where('inventories.quantity_available', '>', 0)
            ->whereNull('inventories.disabled_at');
        })
        ->join('prices', function ($join) use ($user_id) {
            if ($user_id === config('site.apex_user_id')) {
                $join->on('prices.priceable_id', '=', 'items.id')
                ->where('prices.priceable_type', '=', 'App\Models\Item')
                ->where('prices.price_type_id', '=', 3);
            } else {
                if ($this->settings->getGlobal('rep_custom_prices', 'show') === true) {
                    $join->on('prices.priceable_id', '=', 'inventories.id')
                    ->where('prices.priceable_type', '=', 'App\Models\Inventory');
                } else {
                    $join->on('prices.priceable_id', '=', 'items.id')
                    ->where('prices.priceable_type', '=', 'App\Models\Item')
                    ->where('prices.price_type_id', '=', 2);
                }
            }
        });
        if ($eventId) {
            $products = $products->whereIn('products.id', $productIds);
        }
        $products->orderBy($queryStrs['sortBy'], $queryStrs['order'])
        ->groupBy('items.product_id');
        if ($queryStrs['category'] !== null && $queryStrs['category'] !== '') {
            $products = $products->join('product_category', function ($join) use ($queryStrs) {
                $join->on('product_category.product_id', '=', 'products.id')
                ->where('product_category.category_id', '=', $queryStrs['category']);
            });
        }
        $products->with('media');
        $products = $products->paginate($queryStrs['limit']);
        $this->getAvailableInventoryForProducts($products);
        return $products;
    }

    public function getProductWithPrice(int $rep_id, $url, array $eagerLoad = [], $productId = null)
    {
        $product = Product::with($eagerLoad)
        ->with(['inventory' => function ($query) use ($rep_id) {
            $query->where('user_id', $rep_id)
                ->where('quantity_available', '>', 0)
                ->where('disabled_at', '=', null)
                ->with('price');
        }]);

        if (isset($url)) {
            $product->where('slug', $url);
        } else {
            $product->where('id', $productId);
        }

        return $product->first();
    }

    /**
     * Create a new instances of Product
     *
     * @param array $inputs
     * @return bool|Product
     */
    public function create(array $inputs = [])
    {
        $product = new Product;
        $inputs['variant_label'] = 'Print';
        $fields = ['name', 'slug', 'type_id', 'variant_label'];

        if (isset($inputs['long_description'])) {
            $fields[] = 'long_description';
        }
        if (isset($inputs['short_description'])) {
            $fields[] = 'short_description';
        }
        if (isset($inputs['tax_class'])) {
            $fields[] = 'tax_class';
        }
        if (isset($inputs['min'])) {
            $fields[] = 'min';
        }
        if (isset($inputs['max'])) {
            $fields[] = 'max';
        }

        foreach ($fields as $field) {
            $product->$field = $inputs[$field];
        }
        // get user id
        $userId = $this->authRepo->getOwnerId();
        $product->user_id = $userId;
        $user = User::select('pid')->where('id', $userId)->first();
        if ($user !== null) {
            $product->user_pid = $user->pid;
        }
        $product->save();

        foreach ($inputs['items'] as $item) {
            $newItem = $this->itemRepo->create($product->id, $item);
            $this->inventoryRepo->create($userId, $newItem);
        }

        $product->roles()->sync($inputs['roles']);
        foreach ($product->variants as $variant) {
            $variant->visibilities()->sync($inputs['roles']);
        }
        foreach ($inputs['tags'] as $tag) {
            Tag::create([
                'name' => $tag,
                'taggable_type' => Product::class,
                'taggable_id' => $product->id
            ]);
        }

        if (isset($inputs['categories'])) {
            $this->categoryRepo->associate($inputs['categories'], $product);
        } else { // remove associations if exist
            $this->categoryRepo->associate([], $product);
        }

        $product->media()->sync($inputs['images']);

        return $product->load('items', 'tags', 'category', 'media', 'roles', 'type');
    }

    /**
     * Update an instances of Product
     *
     * @param Product $product
     * @param array $inputs
     * @return bool|Product
     */
    public function update($product, $inputs)
    {
        $inputs['variant_label'] = 'Print';
        $fields = ['name', 'slug', 'type_id', 'variant_label'];

        if (isset($inputs['long_description'])) {
            $fields[] = 'long_description';
        }
        if (isset($inputs['short_description'])) {
            $fields[] = 'short_description';
        }
        if (isset($inputs['tax_class'])) {
            $fields[] = 'tax_class';
        }
        if (isset($inputs['min'])) {
            $fields[] = 'min';
        }
        if (isset($inputs['max'])) {
            $fields[] = 'max';
        }

        foreach ($fields as $field) {
            $product->$field = $inputs[$field];
        }

        $product->save();
        $userId = $this->authRepo->getOwnerId();

        foreach ($inputs['items'] as $inputItem) {
            if (isset($inputItem['id'])) {
                $this->itemRepo->update($product, $inputItem);
            } else {
                $newItem = $this->itemRepo->create($product->id, $inputItem);
                $this->inventoryRepo->create($userId, $newItem);
            }
        }

        $product->roles()->sync($inputs['roles']);

        foreach ($product->variants as $variant) {
            $variant->visibilities()->sync($inputs['roles']);
        }

        foreach ($product->tags as $tag) {
            $tag->delete();
        }

        foreach ($inputs['tags'] as $tag) {
            Tag::create([
                'name' => $tag,
                'taggable_type' => Product::class,
                'taggable_id' => $product->id
            ]);
        }

        if (isset($inputs['categories'])) {
            $this->categoryRepo->associate($inputs['categories'], $product);
        } else { // remove associations if exist
            $this->categoryRepo->associate([], $product);
        }

        $product->media()->sync($inputs['images']);
        $product->variants()->doesntHave('items')->delete();
        return $product->load('items', 'tags', 'category', 'media', 'roles', 'type');
    }

    public function findByURL($url, array $eagerLoad = [])
    {
        return Product::with($eagerLoad)->where('slug', $url)->first();
    }

        /**
     * undocumented function summary
     *
     * Undocumented function long description
     *
     * @param type var Description
     * @return {11:return type}
     */
    public function allProductsByUser($user_id)
    {
        return Product::with(['inventory' => function ($query) use ($user_id) {
            $query->with('price', 'item')->where('user_id', $user_id);
        }])->with('media', 'category', 'type')
                    ->whereHas('inventory', function ($query) use ($user_id) {
                        $query->where('user_id', $user_id);
                    })->get();
    }

    public function delete($id)
    {
        $product = Product::find($id);
        $product->update(['name' => $product->name."-".date('Y-m-d'), 'slug' => $product->slug."-".date('Y-m-d')]);
        Product::destroy($id);
        return $product;
    }

    public function search($request, $userId)
    {
        $products = Product::search($request['searchTerm'])
            ->with(['items' => function ($query) use ($userId) {
                $query->with(['inventory' => function ($query) use ($userId) {
                    $query->where('user_id', $userId)
                        ->where('quantity_available', '>', 0);
                }])
                ->whereHas('inventory', function ($query) use ($userId) {
                    $query->where('user_id', $userId)
                        ->where('quantity_available', '>', 0);
                })
                ->with('msrp', 'product');
            }])
            ->whereHas('items.inventory', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->where('quantity_available', '>', 0);
            });

        if (isset($request['order'])) {
            $products = $products->orderBy('name', $request['order']);
        } else {
            $products = $products->orderBy('name', 'ASC');
        }

        if (isset($request['category']) && $request['category'] !== '') {
            $products = $products->whereHas('category', function ($query) use ($request) {
                $query->where('categories.id', '=', $request['category']);
            });
        }

        if (isset($request['limit'])) {
            $products = $products->paginate($request['limit']);
        } else {
            $products = $products->get();
        }

        foreach ($products as $product) {
            $product->default_media = Media::join('mediables', function ($join) use ($product) {
                $join->on('mediables.media_id', '=', 'media.id')
                ->where('mediables.mediable_type', '=', 'App\Models\Product')
                ->where('mediables.mediable_id', '=', $product->id);
            })->take(1)->first();
        }
        return $products;
    }

    public function showWholesale($id)
    {
        $product = Product::where('id', $id)
            ->with(['items' => function ($query) {
                $query->with(['inventory' => function ($query) {
                    $query->where('user_id', config('site.apex_user_id'));
                }])
                ->whereHas('inventory', function ($query) {
                    $query->where('user_id', config('site.apex_user_id'))
                        ->where('quantity_available', '>', 0);
                })
                ->with('wholesalePrice');
            }])
            ->with('media')
            ->whereHas('items.inventory', function ($query) {
                $query->where('user_id', config('site.apex_user_id'));
            })->first();
        return $product;
    }

    private function getAvailableInventoryForProducts($products)
    {
        $totalAvailable = 0;
        foreach ($products as $product) {
            $quantityAvailable = 0;
            foreach ($product->items as $item) {
                if (isset($item->inventory[0])) {
                    if ($item->inventory[0]->expires_at > date('Y-m-d H:i:s') || is_null($item->inventory[0]->expires_at)) {
                        $quantityAvailable += $item->inventory[0]->quantity_available;
                        $product->quantity_available += $quantityAvailable;
                    }
                }
            }
            if ($quantityAvailable > 0) {
                ++$totalAvailable;
            }
        }
        $products->totalAvailable = $totalAvailable;
        return $products;
    }
    /**
     * Return index of product types.
     * 1 Product
     * 2 Subscription
     * 3 Donation
     * 4 Digital
     * 5 Fulfilled by Corporate
     * 6 Business Tools
     *
     * @param array $productTypeIds
     * @return ProductType
     */
    public function productTypes($productTypeIds = [1, 6])
    {
        return ProductType::whereIn('id', $productTypeIds)->get();
    }

    public function all($request)
    {
        if (!isset($request['searchTerm'])) {
            $request['searchTerm'] = '';
        }
        $query = Product::with('media', 'items.wholesalePrice', 'items.msrp', 'items.inventory', 'items.product');
        if (isset($request['user_id'])) {
            $query->where('user_id', $request['user_id']);
        }
        return $query->search($request['searchTerm'], ['name'])
        ->take(10)
        ->get();
    }

    /**
    *
    */
    public function checkMinMax($item_id, $quantity)
    {
        if ($this->authRepo->isOwnerRep()) {
            $item = Item::where('id', $item_id)->with('product')->first();
            if ($item->product->min && $item->product->min > $quantity) {
                return ['error'=> [
                'description' => 'You need a minimum quantity of '.$item->product->min,
                'allowed' => $item->product->min
                    ]
                ];
            }
            if ($item->product->max && $item->product->max < $quantity) {
                return ['error'=> [
                'description' => 'Your maximum quantity is '.$item->product->max,
                'allowed' => $item->product->max
                    ]
                ];
            }
        }
        return true;
    }
}
