<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Models\Product;
use App\Models\Bundle;
use App\Repositories\Contracts\CategoryRepositoryContract;
use App\Repositories\Eloquent\Traits\CommonCrudTrait;

class CategoryRepository implements CategoryRepositoryContract
{
    use CommonCrudTrait;

    /**
     * Get an index of categories.
     *
     * @return Category
     */
    public function index()
    {
        return Category::with('children', 'parent', 'media')->orderby('name')->get();
    }

    /**
     * Get the hierarchy for categories with parent categories on top level.
     *
     * @return Category
     */
    public function getHierarchy()
    {
        return Category::where('level', 0)->with('children', 'media')->orderBy('placement', 'ASC')->get();
    }

    /**
     * Get all the children categories.
     *
     * @return Category $children
     */
    public function getChildren()
    {
        $children = Category::select(
            'categories.id',
            'categories.level',
            'categories.name',
            'categories.parent_id',
            'parent.name as parent_name'
        )->where('categories.level', 1)
        // TODO: This needs to be cleaned up but don't want to mess different places that use this
        ->with('parent')
        ->join(
            'categories as parent',
            'categories.parent_id',
            '=',
            'parent.id'
        )->get();
        return $children;
    }

    /**
     * Create a newly created category in storage.
     *
     * @param array $input
     * @return Category
     */
    public function create(array $input)
    {
        $newCategory = [
            'name' => $input['name'],
            'level' => 0
        ];
        if (isset($input['show_on_store'])) {
            $newCategory['show_on_store'] = $input['show_on_store'];
        }
        $category = Category::create($newCategory);

        if (isset($input['parent_id'])) {
            $parent = Category::where('id', $input['parent_id'])->first();
            $category->level = $parent->level + 1;
            $category->parent_id = $input['parent_id'];
        }

        $lastCategory = Category::where('parent_id', $category->parent_id)
            ->where('level', $category->level)
            ->orderBy('placement', 'DESC')
            ->first();

        if (isset($lastCategory) and $lastCategory->id !== $category->id) {
            $category->placement = $lastCategory->placement + 1;
        }

        $category->save();
        if (isset($input['file'])) {
            $category->media()->attach($input['file']);
            $category->load('media');
        }
        return $category;
    }

    /**
     * Get the specified category.
     *
     * @param  integer $id
     * @return Category
     */
    public function show($id)
    {
        return Category::where('id', $id)->with('media', 'children')->where('level', 0)->first();
    }

    /**
     * Update a specified category.
     *
     * @param array $input
     * @return Category
     */
    public function update(array $input)
    {
        $category = Category::find($input['id']);
        $category->media()->detach();
        if (isset($input['file'])) {
            $category->media()->attach($input['file']);
        }

        $data = ['name' => $input['name']];
        if (isset($input['show_on_store'])) {
            $data['show_on_store'] = $input['show_on_store'];
        }
        $category->update($data);
        $category->load('media');
        return $category;
    }

    /**
     * Associate an object to a category.
     *
     * @param array $categories list of category ids
     * @param Product $object or Bundle $object It can either be a product or bundle.
     * @return Product $object or Bundle $object
     */
    public function associate(array $categories, $object)
    {
        $categoryIds = [];
        foreach ($categories as $categoryId) {
            $category = Category::where('id', $categoryId)->first();
            $categoryIds[] = $category->id;
            while ($category->parent_id !== null) {
                $category = Category::where('id', $category->parent_id)->first();
                $categoryIds[] = $category->id;
            }
        }
        return $object->category()->sync($categoryIds);
    }

    /**
     * Update the placement of a category.
     *
     * @param integer $id
     * @param integer $placement This will be the placement of where you want the category. Placement order starts with 0.
     * @return Category $categories sends back the new order of categories.
     */
    public function placement($categoryId, $placement)
    {
        $originalCategory = Category::find($categoryId);
        $categories = Category::orderBy('placement', 'ASC')
            ->where('parent_id', $originalCategory->parent_id)
            ->get();

        $placementNumber = 0;
        foreach ($categories as $category) {
            if ($placementNumber === $placement) {
                $placementNumber++;
            }
            $category->placement = $placementNumber;
            if ($category->id === $originalCategory->id) {
                $category->placement = $placement;
            } else {
                $placementNumber++;
            }
            $category->save();
        }
        return Category::orderBy('placement', 'ASC')
            ->where('parent_id', $originalCategory->parent_id)
            ->get();
    }

    public function getProductsByCategory(int $categoryId, int $userId)
    {
        return Category::where('id', $categoryId)
            ->with(['media', 'children', 'product.items.inventory'])
            ->where('level', 0)
            ->whereHas('product', function ($productQuery) use ($userId) {
                $productQuery->whereHas('items', function ($itemQuery) use ($userId) {
                    $itemQuery->whereHas('inventory', function ($inventoryQuery) use ($userId) {
                        $inventoryQuery->where('user_id', $userId);
                    })->with(['premiumPrice', 'msrp']);
                });
            })
            ->first();
    }
}
