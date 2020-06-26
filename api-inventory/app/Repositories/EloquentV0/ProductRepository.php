<?php

namespace App\Repositories\EloquentV0;

use App\Repositories\Interfaces\ProductInterface;
use App\Repositories\EloquentV0\ItemRepository;
use App\Repositories\EloquentV0\CategoryRepository;
use App\Repositories\EloquentV0\VariantRepository;
use App\Services\Media\MediaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
use App\Models\Product;
use App\Models\Visibility;
use DB;

class ProductRepository implements ProductInterface
{
    public function __construct()
    {
        $this->CategoryRepo = new CategoryRepository;
        $this->ItemRepo = new ItemRepository;
        $this->VariantRepo = new VariantRepository;
        $this->MediaService = new MediaService;

        $this->paramsTable = [
            'categories' => function ($query, $value, $params) {
                $query->join('product_category', 'products.id', '=', 'product_category.product_id')
                    ->join('categories', 'categories.id', '=', 'product_category.category_id');
                // checks to see if the array of $value has numeric values
                if (count($value) === count(array_filter($value, 'is_numeric'))) {
                    $query->whereIn('categories.id', $value);
                } else {
                    $query->whereIn('categories.name', $value);
                }
            },
            'expands' => function ($query, $expands, $params) {
                foreach ($expands as $expand) {
                    try {
                        $this->expandsTable[$expand]($query, $params);
                    } catch (\Exception $e) {
                    }
                }
            },
            'owner_id' => function ($query, $value, $params) {
                $query->where('products.user_id', $value);
            },
            'price' => function ($query, $value, $params) {
                $this->pricesTable[$params['price']]($query, $params);
            },
            'product_ids' => function ($query, $value, $params) {
                $query->whereIn('products.id', $value);
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
                if ($value === 'created_at' || $value === 'updated_at') {
                    $value = 'products.'.$value;
                }
                return $query->orderBy($value, $inOrder);
            },
            'user_id' => function ($query, $userId, $params) {
                if (isset($userId)) {
                    $query->whereHas('items', function ($query) use ($userId, $params) {
                        $this->ItemRepo->joinInventory($query, $userId, $params['available']);
                    });
                }
            },
            'user_pid' => function ($query, $userPid, $params) {
                if (isset($userPid)) {
                    $query->whereHas('items', function ($query) use ($userPid, $params) {
                        $this->ItemRepo->joinInventoryOnUserPid($query, $userPid, $params['available']);
                    });
                }
            },
            'visibilities' => function ($query, $value, $params) {
                $query->join('product_visibility', 'products.id', '=', 'product_visibility.product_id')
                    ->whereIn('product_visibility.visibility_id', $value);
            },
        ];
        $this->pricesTable = [
            'inventory' => function ($query, $params) {
                $subQuery = DB::table('items')->select(DB::raw('product_id, MIN(inventories.inventory_price) as price'))
                ->whereNull('items.deleted_at')
                ->join('inventories', function ($join) use ($params) {
                    $join->on('inventories.item_id', '=', 'items.id')
                        ->where('inventories.user_id', '=', $params['user_id']);
                    if (isset($params['available'])) {
                        $join->where('quantity_available', filter_var($params['available'], FILTER_VALIDATE_BOOLEAN) ? '>' : '=', 0)
                            ->whereNull('inventories.disabled_at');
                    }
                });
                $subQuery->groupBy('product_id');
                $query->join(DB::raw('('.$subQuery->toSql().') as items_price'), 'products.id', '=', 'items_price.product_id');
                $query->mergeBindings($subQuery);
                $query->addSelect('items_price.price');
                return $query;
            },
            'premium' => function ($query, $params) {
                return $this->joinPrice($query, 'premium');
            },
            'retail' => function ($query, $params) {
                return $this->joinPrice($query, 'retail');
            },
            'wholesale' => function ($query, $params) {
                return $this->joinPrice($query, 'wholesale');
            },
        ];
        $this->expandsTable = [
            'categories' => function ($query, $params) {
                $query->with(['categories' => function ($query) {
                    $query->select(['categories.id', 'categories.name']);
                }]);
            },
            'product_images' => function ($query, $params) {
                $query->with(['images' => function ($query) {
                    $query->select(['media.id', 'url']);
                }]);
            },
            'variants' => function ($query, $params) {
                $query->with(['variants' => function ($query) use ($params) {
                    $this->VariantRepo->standardSelectsAndJoin($query, $params);
                    $this->VariantRepo->getParams($query, $params);
                }]);
            },
            'visibilities' => function ($query, $params) {
                $query->with(['visibilities' => function ($query) {
                    $query->select(['visibilities.id', 'visibilities.name']);
                }]);
            },
        ];
    }

    private function getParams($query, $params)
    {
        if (isset($params['visibilities'])) {
            foreach ($params['visibilities'] as $key => $value) {
                if (!is_numeric($value)) {
                    // Change visibility to an id
                    $params['visibilities'][$key] = (isset(Visibility::MAP[$value]) ? Visibility::MAP[$value] : 0);
                }
            }
        }
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
        $products = Product::select(Product::$selects);
        $this->getParams($products, $params);
        $products->groupBy('products.id');
        $products->distinct();
        return $products->paginate($params['per_page']);
    }

    public function find($params, $id)
    {
        $product = Product::select(Product::$selects)
            ->where('products.id', $id);
        $this->getParams($product, $params);
        return $product->first();
    }

    public function findBySlug($params, $slug)
    {
        $product = Product::select(Product::$selects)
            ->where('products.slug', $slug);
        $this->getParams($product, $params);
        return $product->first();
    }

    public function updateOrCreate($request, $id = null)
    {
        $productModel = new Product();
        $product = Product::updateOrCreate(
            ['id' => $id],
            array_only($request, $productModel->getFillable())
        );
        $eagerLoad = [];
        if (isset($request['images'])) {
            $this->MediaService->attachImages($product, $request['images'], $product->user_id);
            $eagerLoad[] = 'images';
        }
        if (isset($request['categories'])) {
            $categoryIds = [];
            foreach ($request['categories'] as $category) {
                $categoryIds[] = $category['id'];
            }
            $this->associateCategories($product, $categoryIds);
            $eagerLoad[] = 'categories';
        } elseif (isset($request['categoryIds'])) {
            $this->associateCategories($product, $request['categoryIds']);
            $eagerLoad[] = 'categories';
        }
        if (isset($request['visibilities'])) {
            $product->visibilities()->sync(array_pluck($request['visibilities'], 'id'));
            $eagerLoad[] = 'visibilities';
        }
        $product->load($eagerLoad);
        return $product;
    }

    public function delete($id)
    {
        // Product will be prevented from being deleted on the model if there is available inventory.
        return Product::destroy($id);
    }

    private function associateCategories($product, $categoryIds)
    {

        $categoryIds = $this->CategoryRepo->getAssociatedCategories($categoryIds);
        $product->categories()->sync($categoryIds);
        return $product;
    }

    private function joinPrice($query, $type)
    {
        $subQuery = DB::table('items')->select(DB::raw('product_id, MIN('.$type.'_price) as price'))
            ->whereNull('items.deleted_at')
            ->groupBy('product_id');
        $query->join(DB::raw('('.$subQuery->toSql().') as items_price'), 'products.id', '=', 'items_price.product_id');
        $query->mergeBindings($subQuery);
        $query->addSelect('items_price.price');
        return $query;
    }
}
