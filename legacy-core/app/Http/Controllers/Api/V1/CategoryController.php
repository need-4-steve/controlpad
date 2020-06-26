<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\CategoryRepository;
use Illuminate\Http\Response;
use App\Http\Requests\CategoryRequest;

class CategoryController extends Controller
{
    protected $categoryRepo;

    /**
     * Create a new controller instance.
     *
     * @param CategoryRepository $cartRepo
     * @return void
     */
    public function __construct(CategoryRepository $categoryRepo)
    {
        $this->categoryRepo = $categoryRepo;
    }

    /**
     * Get an index of categories.
     *
     * @return Response
     */
    public function index()
    {
        $categories = $this->categoryRepo->index();
        return response()->json($categories, HTTP_SUCCESS);
    }

    /**
     * Get the hierarchy for categories with parent categories on top level.
     *
     * @return Response
     */
    public function getHierarchy()
    {
        $categories = $this->categoryRepo->getHierarchy();
        return response()->json($categories, HTTP_SUCCESS);
    }

    /**
     * Get children categories
     *
     * @return Response
     */
    public function children()
    {
        $children = $this->categoryRepo->getChildren();
        return $children;
    }

    /**
     * Create a newly created category in storage.
     *
     * @return Response
     */
    public function create(CategoryRequest $request)
    {
        $category = $this->categoryRepo->create($request->all());
        return response()->json($category, HTTP_SUCCESS);
    }

    /**
     * Get the specified category.
     *
     * @param  integer $id
     * @return Response
     */
    public function show($id)
    {
        $category = $this->categoryRepo->show($id);
        return response()->json($category, HTTP_SUCCESS);
    }

    /**
     * Update a specified category.
     *
     * @param  integer $id
     * @return Response
     */
    public function update(CategoryRequest $request, $id)
    {
        $category = $this->categoryRepo->update($request->all());
        return response()->json($category, HTTP_SUCCESS);
    }

    /**
     * Update the placement of a category.
     *
     * @param  integer $id
     * @return Response
     */
    public function placement($id)
    {
        $placement = request()->get('placement');
        $categories = $this->categoryRepo->placement($id, $placement);
        return response()->json($categories, HTTP_SUCCESS);
    }

    /**
     * Delete a category.
     *
     * @param  integer $id
     * @return Response
     */
    public function delete($id)
    {
        if ($this->categoryRepo->delete($id)) {
            return response()->json(['message' => 'Category Deleted'], HTTP_SUCCESS);
        }
        return response()->json(['message' => 'Category was not Deleted'], HTTP_SERVER_ERROR);
    }
}
