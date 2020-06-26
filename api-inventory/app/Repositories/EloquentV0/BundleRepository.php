<?php

namespace App\Repositories\EloquentV0;

use App\Repositories\Interfaces\BundleInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
use App\Models\Bundle;
use App\Models\Product;
use App\Models\Price;
use App\Models\Variant;
use App\Models\Visibility;
use App\Services\Media\MediaService;
use DB;

class BundleRepository implements BundleInterface
{
    public function __construct()
    {
        $this->CategoryRepo = new CategoryRepository;
        $this->ItemRepo = new ItemRepository;
        $this->MediaService = new MediaService;

        $this->paramsTable = [
            'bundle_ids' => function ($query, $value, $params) {
                $query->whereIn('bundles.id', $value);
            },
            'categories' => function ($query, $value, $params) {
                $query->join('bundle_category', 'bundles.id', '=', 'bundle_category.bundle_id')
                    ->join('categories', 'categories.id', '=', 'bundle_category.category_id');
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
                    $value = 'bundles.'.$value;
                }
                return $query->orderBy($value, $inOrder);
            },
            'starter_kit' => function ($query, $value, $params) {
                $query->where('starter_kit', '=', filter_var($value, FILTER_VALIDATE_BOOLEAN));
            },
            'user_id' => function ($query, $userId, $params) {
                if (!isset($userId)) {
                    return;
                }
                if (isset($params['available'])) {
                    $query
                    ->join('bundle_item', 'bundle_item.bundle_id', '=', 'bundles.id')
                    ->join('items', 'items.id', '=', 'bundle_item.item_id')
                    ->join('inventories', function ($join) use ($userId) {
                        $join->on('inventories.item_id', '=', 'items.id')
                            ->where('inventories.user_id', $userId);
                    })
                    ->addSelect(DB::raw('FLOOR(MIN(inventories.quantity_available / bundle_item.quantity)) as quantity_available'));
                    if (filter_var($params['available'], FILTER_VALIDATE_BOOLEAN)) {
                        // Available
                        $query->havingRaw('SUM(inventories.quantity_available < bundle_item.quantity) <= 0');
                    } else {
                        // Not Available
                        $query->havingRaw('SUM(inventories.quantity_available < bundle_item.quantity) > 0');
                    }
                    $query->groupBy('bundles.id');
                }
                $query->where('bundles.user_id', $userId);
            },
            'user_pid' => function ($query, $userPid, $params) {
                if (!isset($userPid)) {
                    return;
                }
                if (isset($params['available'])) {
                    $query
                    ->join('bundle_item', 'bundle_item.bundle_id', '=', 'bundles.id')
                    ->join('items', 'items.id', '=', 'bundle_item.item_id')
                    ->join('inventories', function ($join) use ($userPid) {
                        $join->on('inventories.item_id', '=', 'items.id')
                            ->where('inventories.user_pid', $userPid);
                    })
                    ->addSelect(DB::raw('FLOOR(MIN(inventories.quantity_available / bundle_item.quantity)) as quantity_available'));
                    if (filter_var($params['available'], FILTER_VALIDATE_BOOLEAN)) {
                        // Available
                        $query->havingRaw('SUM(inventories.quantity_available < bundle_item.quantity) <= 0');
                    } else {
                        // Not Available
                        $query->havingRaw('SUM(inventories.quantity_available < bundle_item.quantity) > 0');
                    }
                    $query->groupBy('bundles.id');
                }
                $query->where('bundles.user_pid', $userPid);
            },
            'visibilities' => function ($query, $value, $params) {
                $query->join('bundle_visibility', 'bundles.id', '=', 'bundle_visibility.bundle_id')
                    ->whereIn('bundle_visibility.visibility_id', $value);
            },
        ];
        $this->expandsTable = [
            'bundle_images' => function ($query, $params) {
                $query->with(['images' => function ($query) {
                    $query->select(['media.id', 'url']);
                }]);
            },
            'categories' => function ($query, $params) {
                $query->with(['categories' => function ($query) {
                    $query->select(['categories.id', 'categories.name']);
                }]);
            },
            'items' => function ($query, $params) {
                $query->with(['items' => function ($query) use ($params) {
                    $this->ItemRepo->standardSelectsAndJoin($query, $params);
                    if (in_array('variants', $params['expands'])) {
                        $query->with(['variant' => function ($query) use ($params) {
                            $query->select(Variant::$selects);
                            if (in_array('variant_images', $params['expands'])) {
                                $query->with('images');
                            }
                            if (in_array('products', $params['expands'])) {
                                $query->with(['product' => function ($query) use ($params) {
                                    $query->select(Product::$selects);
                                    if (in_array('product_images', $params['expands'])) {
                                        $query->with('images');
                                    }
                                }]);
                            }
                        }]);
                    }
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
        $bundles = Bundle::addSelect(Bundle::$selects);
        $this->getParams($bundles, $params);
        $bundles->groupBy('bundles.id');
        $bundles->distinct();
        $bundles = $bundles->paginate($params['per_page']);
        if (isset($params['expands']) && in_array('items', $params['expands'])) {
            foreach ($bundles as $bundle) {
                foreach ($bundle->items as $item) {
                    $item->quantity = $item->pivot->quantity;
                }
            }
        }
        return $bundles;
    }

    public function find($params, $id)
    {
        $bundle = Bundle::select(Bundle::$selects)
            ->where('bundles.id', $id);
        $this->getParams($bundle, $params);
        $bundle->groupBy('bundles.id');
        $bundle = $bundle->first();
        if (isset($params['expands']) && in_array('items', $params['expands'])) {
            foreach ($bundle->items as $item) {
                $item->quantity = $item->pivot->quantity;
            }
        }
        return $bundle;
    }

    public function updateOrCreate($request, $id = null)
    {
        DB::beginTransaction();
        $bundleModel = new Bundle();
        $bundle = Bundle::updateOrCreate(
            ['id' => $id],
            array_only($request, $bundleModel->getFillable())
        );
        $id = $bundle->id;

        $wholesalePrice = Price::firstOrCreate(
            [
                'price_type_id' => 1,
                'priceable_type' => Bundle::class,
                'priceable_id' => $id,
            ]
        );
        if (isset($request['wholesale_price'])) {
            $wholesalePrice->price = $request['wholesale_price'];
            $wholesalePrice->save();
        }

        $bundle->items()->sync([]);
        if (isset($request['items'])) {
            foreach ($request['items'] as $item) {
                $bundle->items()->attach($item['id'], ['quantity' => $item['quantity']]);
            }
            $bundle = $bundle->with(['items' => function ($query) use ($request, $id) {
                $request['available'] = null;
                $this->ItemRepo->standardSelectsAndJoin($query, array_only($request, ['user_id', 'user_pid', 'available']));
            }]);
        }

        $bundle = $bundle->select(Bundle::$selects)->where('bundles.id', $id)->first();
        $eagerLoad = [];
        if (isset($request['images'])) {
            $this->MediaService->attachImages($bundle, $request['images'], $bundle->user_id);
            $eagerLoad[] = 'images';
        }
        if (isset($request['categories'])) {
            $this->associateCategories($bundle, $request['categories']);
            $eagerLoad[] = 'categories';
        }
        if (isset($request['visibilities'])) {
            $bundle->visibilities()->sync(array_pluck($request['visibilities'], 'id'));
            $eagerLoad[] = 'visibilities';
        }
        $bundle->load($eagerLoad);
        $bundle->wholesale_price = $wholesalePrice->price;

        DB::commit();
        foreach ($bundle->items as $item) {
            $item->quantity = $item->pivot->quantity;
        }
        return $bundle;
    }

    public function delete($id)
    {
        $delete = DB::transaction(function () use ($id) {
            $bundle = Bundle::find($id);
            if (!$bundle) {
                return false;
            }
            $bundle->categories()->detach();
            $bundle->items()->detach();
            $bundle->images()->detach();
            $bundle->visibilities()->detach();
            return Bundle::destroy($id);
        });
        return $delete;

    }

    private function associateCategories($bundle, $categoryIds)
    {
        $categoryIds = $this->CategoryRepo->getAssociatedCategories($categoryIds);
        $bundle->categories()->sync($categoryIds);
        return $bundle;
    }
}
