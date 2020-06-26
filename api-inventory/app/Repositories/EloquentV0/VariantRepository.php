<?php

namespace App\Repositories\EloquentV0;

use App\Repositories\Interfaces\ProductInterface;
use App\Repositories\EloquentV0\ItemRepository;
use App\Repositories\EloquentV0\CategoryRepository;
use App\Services\Media\MediaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
use App\Models\Product;
use App\Models\Variant;
use DB;
use Log;

class VariantRepository implements ProductInterface
{
    public function __construct()
    {
        $this->ItemRepo = new ItemRepository;
        $this->MediaService = new MediaService;
        $this->paramsTable = [
            'expands' => function ($query, $expands, $params) {
                foreach ($expands as $expand) {
                    try {
                        $this->expandsTable[$expand]($query, $params);
                    } catch (\Exception $e) {
                    }
                }
            },
            'price' => function ($query, $value, $params) {
                $this->pricesTable[$value]($query, $params);
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
                    $value = 'variants.'.$value;
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
            'variant_ids' => function ($query, $value, $params) {
                $query->whereIn('variants.id', $value);
            },
            'item_ids' => function ($query, $value, $params) {
                $query->whereIn('id', function ($query) use ($value) {
                    $query->select('variant_id')
                    ->from('items')
                    ->whereIn('id', $value);
                });
            },
            'visibilities' => function ($query, $value, $params) {
                // Do nothing, deprecated
            },
        ];
        $this->expandsTable = [
            'product' => function ($query, $params) {
                $query->with(['product' => function ($query) use ($params) {
                    $query->select(Product::$selects);
                    if (in_array('product_images', $params['expands'])) {
                        $query->with('images');
                    }
                }]);
            },
            'variant_images' => function ($query, $params) {
                $query->with(['images' => function ($query) {
                    $query->select(['media.id', 'url']);
                }]);
            },
            'visibilities' => function ($query, $params) {
                $query->with(['visibilities' => function ($query) {
                    $query->select(['visibilities.id', 'visibilities.name']);
                }]);
            },
        ];
        $this->pricesTable = [
            'inventory' => function ($query, $params) {
                $query->join('items', 'items.variant_id', '=', 'variants.id');
                if (isset($params['user_id'])) {
                    $this->ItemRepo->joinInventory($query, $params['user_id'], $params['available'], 'prices');
                } else {
                    $this->ItemRepo->joinInventoryOnUserPid($query, $params['user_pid'], $params['available'], 'prices');
                }
                $query->groupBy('variants.id');
                $query->addSelect(DB::raw('min(inventories.inventory_price) as price'));
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
    }

    public function getParams($query, $params)
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
        $items = Variant::select();
        $this->getParams($items, $params);
        $this->standardSelectsAndJoin($items, $params);
        $items->distinct();
        return $items->paginate($params['per_page']);
    }

    public function find($params, $id)
    {
        $variant = Variant::where('variants.id', $id);
        $this->standardSelectsAndJoin($variant, $params);
        $this->getParams($variant, $params);
        return $variant->first();
    }

    public function updateOrCreate($request, $id = null)
    {
        $variantModel = new Variant;
        $variant = Variant::updateOrCreate(
            ['id' => $id],
            array_only($request, $variantModel->getFillable())
        );
        $eagerLoad = [];
        if (isset($request['images'])) {
            $this->MediaService->attachImages($variant, $request['images'], $variant->product->user_id);
            $eagerLoad[] = 'images';
        }
        if (isset($request['visibilities'])) {
            $variant->visibilities()->sync(array_pluck($request['visibilities'], 'id'));
            $eagerLoad[] = 'visibilities';
        }
        $variant->load($eagerLoad);
        return $variant;
    }

    public function delete($id)
    {
        // Variant will be prevented from being deleted on the model if there is available inventory.
        return Variant::destroy($id);
    }

    public function standardSelectsAndJoin($query, $params)
    {
        $query->select(Variant::$selects);
        $query->with(['items' => function ($query) use ($params) {
            $this->ItemRepo->standardSelectsAndJoin($query, $params);
            if (array_key_exists('search_term', $params)) {
                $this->paramsTable['search_term']($query, $params['search_term'], []);
            }
        }]);
        // Filters out variants that don't have items exist when searching
        if (array_key_exists('search_term', $params)) {
            $query->whereHas('items', function ($query) use ($params) {
                $this->paramsTable['search_term']($query, $params['search_term'], []);
            });
        }
        $query->distinct();
        return $query;
    }

    private function joinPrice($query, $type)
    {
        $subQuery = DB::table('items')->select(DB::raw('variant_id, MIN('.$type.'_price) as price'))
            ->whereNull('items.deleted_at')
            ->groupBy('variant_id');
        $query->join(DB::raw('('.$subQuery->toSql().') as variant_price'), 'variants.id', '=', 'variant_price.variant_id');
        $query->mergeBindings($subQuery);
        $query->addSelect('variant_price.price');
        return $query;
    }
}
