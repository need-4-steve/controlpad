<?php

namespace App\Repositories\Eloquent;

use App\Models\Bundle;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\Tag;
use App\Models\Category;
use App\Models\Media;
use App\Models\User;
use App\Repositories\Contracts\BundleRepositoryContract;
use App\Repositories\Eloquent\Traits\CommonCrudTrait;
use App\Repositories\Eloquent\PriceRepository;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Eloquent\AuthRepository;
use App\Repositories\Eloquent\InventoryRepository;

use DB;

class BundleRepository implements BundleRepositoryContract
{
    use CommonCrudTrait;

    public function __construct(PriceRepository $priceRepo, CategoryRepository $categoryRepo, AuthRepository $authRepo, InventoryRepository $inventoryRepo)
    {
        $this->priceRepo = $priceRepo;
        $this->categoryRepo = $categoryRepo;
        $this->authRepo = $authRepo;
        $this->inventoryRepo =$inventoryRepo;
    }

    public function index(array $request = [
        'category' => 'all',
        'searchTerm' => '',
        'per_page' => 10,
        'order' => 'DESC',
        'column' => 'updated_at'
    ])
    {
        $bundle = Bundle::with('items', 'tags', 'category', 'media', 'roles', 'wholesalePrice')
            ->where('user_id', $this->authRepo->getOwnerId())
            ->search($request['search_term']);
        if ($request['category'] !== 'all') {
            $bundle->whereHas('category', function ($query) use ($request) {
                $query->where('categories.id', '=', $request['category']);
            });
        }
        $bundle->orderBy($request['column'], $request['order']);
        if (isset($request['per_page'])) {
            return $bundle->paginate($request['per_page']);
        }
        return $bundle->paginate(100);
    }

    /**
     * Create a new instances of BundleRepository
     *
     * @param array $inputs
     * @return bool|BundleRepository
     */
    public function create(array $inputs = [])
    {
        $bundle = new Bundle;
        $inputs['user_id'] = $this->authRepo->getOwnerId();
        $fields = [
            'name',
            'slug',
            'short_description',
            'long_description',
            'user_id',
            'starter_kit',
            'tax_class',
            'wholesale_price',
        ];

        foreach ($fields as $field) {
            $bundle->$field = array_get($inputs, $field);
        }
        $user = User::select('pid')->where('id', $inputs['user_id'])->first();
        if ($user != null) {
            $bundle->user_pid = $user->pid;
        }
        $bundle->save();

        foreach ($inputs['items'] as $item) {
            $bundle->items()->attach($item['id'], ['quantity' => $item['quantity']]);
        }

        foreach ($bundle->getProducts() as $product) {
            if ($product->type_id === 5) { // Product Type 5 = Fulfilled by Corporate
                $product->duration = $inputs['duration'];
                $product->save();
                $bundle->type_id = 2; // Bundle Type 2 = Fulfilled by Corporate
                $bundle->save();
            }
        }

        $bundle->roles()->sync($inputs['roles']);

        foreach ($inputs['tags'] as $tag) {
            Tag::create([
                'name' => $tag,
                'taggable_type' => Bundle::class,
                'taggable_id' => $bundle->id
            ]);
        }

        $this->categoryRepo->associate($inputs['categories'], $bundle);

        $bundle->media()->sync($inputs['images']);

        $this->priceRepo->create($bundle->id, '1', $inputs['wholesale_price'], Bundle::class);

        return $bundle->load('items', 'tags', 'category', 'media', 'roles', 'wholesalePrice', 'prices');
    }

    /**
     * Update an instances of BundleRepository
     *
     * @param BundleRepository $bundlerepository
     * @param array $inputs
     * @return bool|BundleRepository
     */
    public function update(Bundle $bundle, array $inputs = [])
    {
        $fields = [
            'name',
            'slug',
            'short_description',
            'long_description',
            'starter_kit',
            'tax_class',
            'wholesale_price',
        ];

        foreach ($fields as $field) {
            $bundle->$field = array_get($inputs, $field);
        }

        $bundle->items()->detach();

        foreach ($inputs['items'] as $item) {
            if (isset($item['quantity'])) {
                $bundle->items()->attach($item['id'], ['quantity' => $item['quantity']]);
            }
        }

        foreach ($bundle->getProducts() as $product) {
            if ($product->type_id === 5) { // Product Type 5 = Fulfilled by Corporate
                $product->duration = $inputs['duration'];
                $product->save();
                $bundle->type_id = 2; // Bundle Type 2 = Fulfilled by Corporate
            }
        }
        $bundle->save();

        $bundle->roles()->sync($inputs['roles']);

        foreach ($bundle->tags as $tag) {
            $tag->delete();
        }

        foreach ($inputs['tags'] as $tag) {
            Tag::create([
                'name' => $tag,
                'taggable_type' => Bundle::class,
                'taggable_id' => $bundle->id
            ]);
        }

        $this->categoryRepo->associate($inputs['categories'], $bundle);

        $bundle->media()->sync($inputs['images'], 'id');

        if ($inputs['wholesale_price']) {
            $this->priceRepo->update($bundle->id, '1', $inputs['wholesale_price'], Bundle::class);
        }
        return $bundle->load('items', 'tags', 'category', 'media', 'roles', 'wholesalePrice', 'prices');
    }

    public function show($id)
    {
        return Bundle::where('id', $id)->with('tags', 'items.product', 'category', 'media', 'roles', 'wholesalePrice')->first();
    }

    public function getBundlesByRole($input, $role_id, $type = 1)
    {
        $settings = app('globalSettings');

        $bundles = Bundle::select('bundles.id', 'bundles.name', 'prices.price', DB::raw('SUM(inventories.quantity_available < bundle_item.quantity) AS inv_count'))
                        ->join('bundle_item', 'bundle_item.bundle_id', '=', 'bundles.id')
                        ->join('items', 'items.id', '=', 'bundle_item.item_id')
                        ->join('inventories', 'inventories.item_id', '=', 'items.id')
                        ->join('prices', 'prices.priceable_id', '=', 'bundles.id')
                        ->join('bundle_visibility', 'bundle_visibility.bundle_id', '=', 'bundles.id')
                        ->join('roles', 'roles.id', '=', 'bundle_visibility.visibility_id');
        if ($input['category'] !== null && $input['category'] !== '') {
            $bundles = $bundles->join('bundle_category', 'bundle_category.bundle_id', '=', 'bundles.id')
                        ->join('categories', 'categories.id', '=', 'bundle_category.category_id')
                        ->where('categories.id', '=', $input['category']);
        }
        $bundles = $bundles->where('bundles.type_id', $type)
                        ->where('inventories.user_id', config('site.apex_user_id'))
                        ->where('inventories.owner_id', config('site.apex_user_id'))
                        ->where('prices.priceable_type', 'App\Models\Bundle')
                        ->where('prices.price_type_id', 1) // 1 = wholesale price
                        ->where('visibility_id', $role_id);

        $bundles = $bundles->with('category.parent', 'media')
                        ->groupBy('bundles.id')
                        ->orderBy($input['sortBy'], $input['order']);

        if ($settings->getGlobal('new_wholesale', 'show')) {
            $bundles = $bundles->havingRaw('SUM(inventories.quantity_available < bundle_item.quantity) <= 0')
                ->paginate($input['per_page']);
        } else {
            $bundles = $bundles
                        ->havingRaw('inv_count <= 0')
                        ->get();
            return ['data' => $bundles, 'total' => count($bundles)];
        }

        return $bundles;
    }

    public function getBundleAsProducts($id)
    {
        // For a bundle to be editable a bundle needs to be returned in the format of products with their items.
        $bundle = Bundle::where('id', $id)->with(
            'items.product.items.wholesalePrice',
            'items.product.media',
            'media',
            'tags',
            'category.parent',
            'roles',
            'wholesalePrice'
        )
        ->first();
        if ($bundle === null) {
            return null;
        }

        // Foreach item in a bundle it finds the product associated with the item and puts it into it's own array of products.
        // Makes it so you don't return duplicate products.
        $products = null;
        foreach ($bundle['items'] as $item) {
            $products[$item['product']->id] = $item['product'];

            // Foreach item in a product it associates the bundle item quantity if there is one or sets it to 0.
            foreach ($products[$item['product']->id]['items'] as $productItem) {
                if ($productItem->id === $item->id) {
                    $productItem->quantity = $item->pivot->quantity;
                } elseif (!$productItem->quantity) {
                    $productItem->quantity = 0;
                }
            }
        }

        // Returns it in the format of products with their items.
        // Items will only have quantity associated to it only if the item was part of the bundle.
        return ['bundle' => $bundle, 'products' => $products];
    }

    public function starterKits()
    {
        $bundles = Bundle::where('starter_kit', true)->whereHas('items')->with('wholesalePrice')->get();
        $bundles = $this->checkInventory($bundles);
        return $bundles;
    }

    private function checkInventory($bundles)
    {
        DB::beginTransaction();
        // Unsets Bundles that don't have enough inventory in stock.
        foreach ($bundles as $key => $bundle) {
            $bundleQuantity = 0;
            $firstItem = $bundle->items->first();
            // set lowest quantity to the first item quantity
            $lowestQuantity = Inventory::where('item_id', $firstItem->id)->where('user_id', config('site.apex_user_id'))->where('owner_id', config('site.apex_user_id'))->first();
            if ($lowestQuantity === null) {
                unset($bundles[$key]);
                continue;
            }
            $lowestQuantity = $lowestQuantity->quantity_available /
                                $firstItem->pivot->quantity;

            foreach ($bundle->items as $item) {
                $quantityInBundle = $item->pivot->quantity;
                $quantity = Inventory::where('item_id', $item->id)->where('user_id', config('site.apex_user_id'))->where('owner_id', config('site.apex_user_id'))->first();
                if ($quantity === null) {
                    unset($bundles[$key]);
                    continue 2;
                };
                $quantity = $quantity->quantity_available;
                // get lowest integer of quantity we have
                $currentMaxBundleByItem = floor($quantity / $quantityInBundle);

                if ($currentMaxBundleByItem < $lowestQuantity) {
                    $lowestQuantity = $currentMaxBundleByItem;
                }
            }

            if ($lowestQuantity <= 0) {
                unset($bundles[$key]);
            }
        }
        DB::commit();
        return $bundles;
    }
}
