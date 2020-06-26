<?php

namespace App\Repositories\Eloquent;

use App\Models\Item;
use App\Models\User;
use App\Models\Product;
use App\Models\Variant;
use App\Repositories\Contracts\ItemRepositoryContract;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\Traits\CommonCrudTrait;

class ItemRepository implements ItemRepositoryContract
{
    use CommonCrudTrait;

    /* @var ItemRepositoryContract */
    protected $itemRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
        $this->settings = app('globalSettings');
    }

    /**
     * Create a new instances of Item
     *
     * @param array $inputs
     * @return bool|Item
     */
    public function create($productId, array $inputs = [])
    {
        $item = new item;
        $priceRepo = new PriceRepository;
        $item->product_id = $productId;
        $fields = [
            'size',
            'print',
            'weight',
            'length',
            'width',
            'height',
            'custom_sku',
            'is_default',
            'premium_shipping_cost',
            'manufacturer_sku'
        ];
        if (isset($inputs['location'])) {
            $fields[] = 'location';
        }
        if (!isset($inputs['print'])) {
            $inputs['print'] = '';
        }
        foreach ($fields as $field) {
            $item->$field = array_get($inputs, $field);
        }
        $variant = Variant::firstOrCreate([
            'product_id'   => $item->product_id,
            'name'         => isset($item->print) ? $item->print : '',
            'option_label'  => 'Size'
        ]);
        $item->variant_id = $variant->id;
        $item->save();

        $priceRepo->create($item->id, '1', $inputs['wholesale_price']['price'], Item::class);
        $priceRepo->create($item->id, '2', $inputs['msrp']['price'], Item::class);
        $priceRepo->create($item->id, '3', $inputs['premium_price']['price'], Item::class);

        return $item;
    }

    /**
     * Update an instances of Item
     *
     * @param Item $item
     * @param array $inputs
     * @return bool|Item
     */
    public function update(Product $product, $inputs)
    {
        $priceRepo = new PriceRepository;
        $item = Item::find($inputs['id']);
        $item->update($inputs);
        $item->product_id = $product->id;
        $variant = Variant::firstOrCreate([
            'product_id'   => $item->product_id,
            'name'         => isset($item->print) ? $item->print : '',
            'option_label'  => 'Size'
        ]);
        $item->variant_id = $variant->id;
        $item->save();

        $priceRepo->update($item->id, '1', $inputs['wholesale_price']['price'], Item::class);
        $priceRepo->update($item->id, '2', $inputs['msrp']['price'], Item::class);
        $priceRepo->update($item->id, '3', $inputs['premium_price']['price'], Item::class);
        return $item;
    }

    // gets wholesale only
    public function getItemsByProductAndInventory($product_id, $user_id)
    {
        $items = Item::select('items.id as id', 'items.size', 'inventories.quantity_available', 'prices.price')
            ->join('inventories', 'items.id', '=', 'inventories.item_id')
            ->join('prices', 'priceable_id', '=', 'items.id')
            ->where('product_id', $product_id)
            ->where('prices.priceable_type', '=', 'App\Models\Item')
            ->where('prices.price_type_id', '=', 1)
            ->where('inventories.user_id', '=', $user_id)
            ->where('inventories.quantity_available', '>', 0)
            ->get();
        return $items;
    }

    public function getItemsWithWholesalePrice($itemIds, $userId, $ownerId)
    {
        $items = Item::select(
            'items.id as id',
            'items.product_id',
            'inventories.id as inventory_id',
            'prices.price',
            'products.name as product_name',
            'products.min',
            'products.max',
            'products.tax_class',
            'variants.min as variant_min',
            'variants.max as variant_max',
            'items.variant_id',
            'items.size as option',
            'items.manufacturer_sku as sku',
            'variants.name as variant_name',
            'variants.option_label as variant_option_label',
            'items.premium_shipping_cost',
            'items.weight',
            'products.variant_label'
        )
            ->join('variants', 'variants.id', '=', 'items.variant_id')
            ->join('products', 'products.id', '=', 'items.product_id')
            ->join('prices', 'priceable_id', '=', 'items.id')
            ->leftJoin('inventories', 'inventories.item_id', '=', 'items.id')
            ->where('inventories.user_id', '=', $userId)
            ->where('inventories.owner_id', '=', $ownerId)
            ->where('prices.priceable_type', '=', 'App\Models\Item')
            ->where('prices.price_type_id', '=', 1)
            ->whereIn('items.id', $itemIds)
            ->get();
        return $items;
    }

    public function getItemsWithRetailPrice($itemIds, $userId, $ownerId)
    {
        $items = Item::select(
            'items.id as id',
            'inventories.id as inventory_id',
            'items.product_id',
            'items.variant_id',
            'prices.price',
            'products.name as product_name',
            'products.tax_class',
            'items.size as option',
            'items.manufacturer_sku as sku',
            'variants.name as variant_name',
            'variants.option_label as variant_option_label',
            'items.premium_shipping_cost',
            'items.weight',
            'products.variant_label'
        )
            ->join('variants', 'variants.id', '=', 'items.variant_id')
            ->join('products', 'products.id', '=', 'items.product_id')
            ->join('prices', 'priceable_id', '=', 'items.id')
            ->leftJoin('inventories', 'inventories.item_id', '=', 'items.id')
            ->where('inventories.user_id', '=', $userId)
            ->where('inventories.owner_id', '=', $ownerId)
            ->where('prices.priceable_type', '=', 'App\Models\Item')
            ->where('prices.price_type_id', '=', 2)
            ->whereIn('items.id', $itemIds)
            ->get();
        if (count($items) != count($itemIds)) {
            \Log::error('Failed to getItemsWithRetailPrice.', ['itemsIds' => json_encode($itemIds)]);
            abort(500);
        }
        return $items;
    }

    public function delete($id)
    {
        $item = Item::find($id);
        if ($item->is_default == true) {
            $defaultItem = Product::where('id', $item['product_id'])->first()->items()->where('is_default', false)->first();
            if (isset($defaultItem)) {
                $defaultItem->update(['is_default' => true]);
            }
        }
        $item->update(['custom_sku' => $item->custom_sku."-".date('Y-m-d'), 'manufacturer_sku' => $item->manufacturer_sku."-".date('Y-m-d')]);
        $item->inventory()->delete();
        Item::destroy($id);
        return $item;
    }

    public function updateVariantClaimNumber()
    {
        Variant::query()->update(['name' => \DB::raw('(variants.id + 1000)')]);
        Item::query()->update(['print' => \DB::raw('(items.variant_id + 1000)')]);
    }
}
