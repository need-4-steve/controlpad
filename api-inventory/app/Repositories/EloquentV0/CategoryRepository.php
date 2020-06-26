<?php

namespace App\Repositories\EloquentV0;

use App\Repositories\Interfaces\CategoryInterface;
use App\Repositories\EloquentV0\ItemRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
use App\Models\Category;
use DB;

class CategoryRepository implements CategoryInterface
{
    /**
     * Get an index of categories.
     *
     * @return Category
     */
    public function index()
    {
        return Category::where('level', 0)->with('children')->orderBy('placement', 'ASC')->get();
    }

    public function find($id)
    {
        $category = Category::find($id);
        return $category;
    }

    public function create($request)
    {
        $category = Category::create($request);
        if (isset($request['parent_id'])) {
            $parent = Category::where('id', $request['parent_id'])->first();
            $category->level = $parent->level + 1;
            $category->save();
        }
        return $category;
    }

    public function update($request, $id)
    {
        $category = Category::find($id);
        if (isset($request['parent_id']) && $request['parent_id'] != $category->parent_id) {
            $parent = Category::where('id', $request['parent_id'])->first();
            $request['level'] = $parent->level + 1;
        }
        // an error would be thrown otherwise if the parent_id is not part of the request
        try {
            if (is_null($request['parent_id'])) {
                $request['level'] = 0;
            }
        } catch (\Exception $e) {
        }
        $category->update($request);
        return $category;
    }

    public function delete($id)
    {
        return Category::destroy($id);
    }

    public function getAssociatedCategories($categoryIdsArray)
    {
        $categories = Category::whereIn('id', $categoryIdsArray)->get()->toArray();
        $ids = [];
        foreach ($categories as $category) {
            $ids[$category['id']] = $category['id'];
            if (!is_null($category['parent_id'])) {
                $ids[$category['parent_id']] = $category['parent_id'];
            }
        }
        return $ids;
    }
}
