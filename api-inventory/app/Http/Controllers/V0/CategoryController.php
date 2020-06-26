<?php

namespace App\Http\Controllers\V0;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Repositories\Interfaces\CategoryInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Requests\FormRequest;
use App\Http\Requests\Category\CreateRequest;
use App\Http\Requests\Category\UpdateRequest;

class CategoryController extends Controller
{
    protected $CategoryRepo;

    public function __construct(CategoryInterface $CategoryRepo)
    {
        $this->CategoryRepo = $CategoryRepo;
    }

    public function index(Request $request)
    {
        $category = $this->CategoryRepo->index();
        return response()->json($category, 200);
    }

    public function find(Request $request, $id)
    {
        $category = $this->CategoryRepo->find($id);
        if (!$category) {
            return response()->json(['error' => 'Unable to find an category'], 404);
        }
        return response()->json($category, 200);
    }

    public function create(Request $request)
    {
        $this->validateRequest(new CreateRequest, $request);
        $category = $this->CategoryRepo->create($request->all());
        return response()->json($category, 200);
    }

    public function update(Request $request, $id)
    {
        $this->validateRequest(new UpdateRequest, $request, $id);
        $category = $this->CategoryRepo->update($request->all(), $id);
        return response()->json($category, 200);
    }

    public function delete(Request $request, $id)
    {
        $category = $this->CategoryRepo->delete($id);
        return response()->json('Success', 200);
    }
}
