<?php

namespace App\Repositories\EloquentV0;

use App\Repositories\Interfaces\ItemInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
use App\Models\Item;
use App\Models\Inventory;
use App\Models\Price;
use App\Models\Product;
use App\Models\Variant;
use App\Repositories\EloquentV0\ProductRepository;
use App\Repositories\EloquentV0\VariantRepository;
use DB;

class ItemRepository implements ItemInterface
{
    public function __construct()
    {
        $this->paramsTable = [
            'expands' => function ($query, $expands, $params) {
                foreach ($expands as $expand) {
                    try {
                        $this->expandsTable[$expand]($query, $params);
                    } catch (\Exception $e) {
                    }
                }
            },
            'item_ids' => function ($query, $value, $params) {
                $query->whereIn('items.id', $value);
            },
            'search_term' => function ($query, $value, $params) {
                $check = true;
                // Search throws an error for weird double quote issues. Examples: "" "", ""a"", "";;;"".
                if (preg_match('/"/', $value)) {
                    $searchTerm = preg_replace('/[^A-Za-z0-9]/', '', $value);
                    if (strlen($searchTerm) < 2) {
                        $check = false;
                    }
                }
                if (!empty(trim($value)) && $check) {
                    $query->search($value);
                    $query->addSelect('relevance');
                }
            },
            'sort_by' => function ($query, $value, $params) {
                $inOrder = 'ASC';
                if (strpos($value, '-') === 0) {
                    $inOrder = 'DESC';
                    $value = str_replace('-', '', $value);
                }
                switch ($value) {
                    case 'created_at':
                    case 'updated_at':
                        $value = 'items.'.$value;
                        break;
                    case 'quantity_available':
                        $value = 'inventories.quantity_available';
                        break;
                    case 'product_name':
                        $query->join('products', 'products.id', 'items.product_id');
                        $value = 'products.name';
                        break;
                }
                return $query->orderBy($value, $inOrder);
            },
        ];
        $this->expandsTable = [
            'variant' => function ($query, $params) {
                $query->with(['variant' => function ($query) use ($params) {
                    $query->select(Variant::$selects);
                    if (in_array('variant_images', $params['expands'])) {
                        $query->with('images');
                    }
                    if (in_array('product', $params['expands'])) {
                        $query->with(['product' => function ($query) use ($params) {
                            $query->select(Product::$selects);
                            if (in_array('product_images', $params['expands'])) {
                                $query->with('images');
                            }
                        }]);
                    }
                }]);
            },
        ];
    }

    private function getParams($query, $params)
    {
        foreach ($params as $param => $value) {
            try {
                $this->paramsTable[$param]($query, $value, $params);
            } catch (\Exception $e) {
            }
        }
        return $query;
    }

    public function index($params)
    {
        $items = Item::select();
        $this->getParams($items, $params);
        $this->standardSelectsAndJoin($items, $params);
        $items->distinct();
        return $items->paginate($params['per_page']);
    }

    public function find($id, $params)
    {
        $item = Item::where('items.id', $id);
        $this->getParams($item, $params);
        $this->standardSelectsAndJoin($item, $params);
        return $item->first();
    }

    public function create($request)
    {
        DB::beginTransaction();
        $variant = Variant::find($request['variant_id']);
        $product = Product::find($variant->product_id);
        $request['product_id'] = $product->id;
        $request['print'] = $variant->name;
        $item = Item::create($request);
        $now = date('Y-m-d H:i:s');
        $pricesArray = [];
        $prices = array_only($request, ['wholesale_price', 'retail_price', 'premium_price']);
        foreach ($prices as $priceType => $price) {
            $pricesArray[] = [
                'price_type_id' => $this->getPriceTypeId($priceType),
                'priceable_type' => 'App\Models\Item',
                'priceable_id' => $item->id,
                'price' => $price,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $item[$priceType] = $price;
        }
        $prices = DB::table('prices')->insert($pricesArray);
        // Automatically create inventory for the product owner.
        $inventory = $this->updateOrCreateInventory(
            ['user_id' => $product->user_id, 'quantity' => 0],
            $item->id
        );
        $item['option'] = $item->size;
        $item['sku'] = $item->manufacturer_sku;
        DB::commit();
        return $item;
    }

    public function update($request, $id = null)
    {
        if (isset($request['variant_id'])) {
            $variant = Variant::find($request['variant_id']);
            $request['product_id'] = $variant->product_id;
            $request['print'] = $variant->name;
        }
        $itemModel = new Item;
        $item = Item::updateOrCreate(
            ['id' => $id],
            array_only($request, $itemModel->getFillable())
        );
        $prices = array_only($request, ['wholesale_price', 'retail_price', 'premium_price']);
        DB::beginTransaction();
        foreach ($prices as $key => $price) {
            $priceTypeId = $this->getPriceTypeId($key);
            $price = $this->updateOrCreatePrice($id, $price, $priceTypeId, 'App\Models\Item');
        }
        DB::commit();
        $item->option = $item->size;
        $item->sku = $item->manufacturer_sku;
        return $item;
    }

    public function delete($id)
    {
        // Item will be prevented from being deleted on the model if there is available inventory.
        return Item::destroy($id);
    }

    public function updateOrCreateInventory($request, $id)
    {
        $update = [];
        if (isset($request['disable'])) {
            if (filter_var($request['disable'], FILTER_VALIDATE_BOOLEAN)) {
                $update = ['disabled_at' => date('Y-m-d H:i:s')];
            } else {
                $update = ['disabled_at' => null];
            }
        }
        // Append user_pid if needed to allow old api calls to ommit user_pid
        if (!isset($request['user_pid'])) {
            $update['user_pid'] = (new \App\Services\User\UserService)->getPidForId($request['user_id']);
            $update['owner_pid'] = $update['user_pid'];
        }
        if (isset($request['inventory_price'])) {
            $update['inventory_price'] = $request['inventory_price'];
        }
        DB::beginTransaction();
        $inventory = Inventory::updateOrCreate(
            [
                'user_id' => $request['user_id'],
                'owner_id' => $request['user_id'],
                'item_id' => $id,
            ],
            $update
        );
        if ($request['quantity'] !== 0) {
            $inventory->increment('quantity_available', $request['quantity']);
        }
        if (isset($request['inventory_price'])) {
            $inventory->inventory_price = $this->updateOrCreatePrice($inventory->id, $request['inventory_price'], 4, 'App\Models\Inventory');
        } else {
            $inventory->inventory_price = Price::where('priceable_type', '=', 'App\Models\Inventory')
                ->where('priceable_id', '=', $inventory->id)
                ->where('price_type_id', 4)
                ->value('price');
        }
        DB::commit();
        $inventory->disable = is_null($inventory->disabled_at) ? false : true;
        return $inventory;
    }

    private function updateOrCreatePrice($priceableId, $price, $priceTypeId, $class)
    {
        $price = Price::updateOrCreate(
            [
                'price_type_id' => $priceTypeId,
                'priceable_type' => $class,
                'priceable_id' => $priceableId,
            ],
            [
                'price' => $price,
            ]
        );
        return $price['price'];
    }

    public function standardSelectsAndJoin($query, $params)
    {
        $userId = isset($params['user_id']) ? $params['user_id'] : null;
        $userPid = isset($params['user_pid']) ? $params['user_pid'] : null;
        $query->addSelect($this->getSelects($query, $userId, $userPid));
        if (isset($params['user_id'])) {
            $this->joinInventory($query, $params['user_id'], $params['available']);
        } elseif (isset($params['user_pid'])) {
            $this->joinInventoryOnUserPid($query, $params['user_pid'], $params['available']);
        }
        return $query;
    }

    private function getSelects($query, $userId = null, $userPid = null)
    {
        $selects = [
            'items.id',
            'variant_id',
            'size as option',
            'manufacturer_sku as sku',
            'location',
            'weight',
            'items.wholesale_price',
            'items.retail_price',
            'items.premium_price',
            'premium_shipping_cost',
            'items.created_at',
            'items.updated_at',
        ];
        if (isset($userId) || isset($userPid)) {
            $selects = array_merge($selects, [
                'inventories.id as inventory_id',
                'inventories.quantity_available',
                'inventories.inventory_price',
                'inventories.user_id',
                DB::raw("CASE WHEN inventories.disabled_at IS NULL THEN 0 ELSE 1 END AS disable")
            ]);
        }
        return $selects;
    }

    public function joinInventory($query, $userId, $available, $priceName = 'inventory_price')
    {
        $query->join('inventories', 'inventories.item_id', '=', 'items.id')
            ->where('inventories.user_id', '=', $userId);
        if ($available !== null) {
            // > 0 is available, = 0 is not
            $query->where('quantity_available', filter_var($available, FILTER_VALIDATE_BOOLEAN) ? '>' : '=', 0)
              ->whereNull('inventories.disabled_at');
        }
        return $query;
    }

    public function joinInventoryOnUserPid($query, $userPid, $available, $priceName = 'inventory_price')
    {
        $query->join('inventories', 'inventories.item_id', '=', 'items.id')
            ->where('inventories.user_pid', '=', $userPid);
        if ($available !== null) {
            // > 0 is available, = 0 is not
            $query->where('quantity_available', filter_var($available, FILTER_VALIDATE_BOOLEAN) ? '>' : '=', 0)
              ->whereNull('inventories.disabled_at');
        }

        return $query;
    }

    public function getPriceTypeId($priceTypeName)
    {
        switch ($priceTypeName) {
            case 'wholesale_price':
                return 1;
            case 'retail_price':
                return 2;
            case 'premium_price':
                return 3;
        }
    }
}
