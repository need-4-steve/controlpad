<?php

namespace App\Http\Controllers\V0;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Repositories\Interfaces\ProductInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Requests\FormRequest;
use App\Http\Requests\Products\CreateRequest;
use App\Http\Requests\Products\DeleteRequest;
use App\Http\Requests\Products\FindRequest;
use App\Http\Requests\Products\IndexRequest;
use App\Http\Requests\Products\UpdateRequest;
use Log;

class ProductController extends Controller
{
    protected $ProductRepo;

    public function __construct(ProductInterface $ProductRepo)
    {
        $this->ProductRepo = $ProductRepo;
    }

    public function index(Request $request)
    {
        $this->validateRequest(new IndexRequest, $request);
        $request['available'] = $request->input('available', null);
        $request['per_page'] = $request->input('per_page', 100);
        $request['sort_by'] = $request->input('sort_by', 'name');
        $request['user_id'] = $request->input('user_id', null);
        $request['user_pid'] = $request->input('user_pid', null);
        $products = $this->ProductRepo->index($request->all());
        return response()->json($products, 200);
    }

    public function find(Request $request, $id)
    {
        $this->validateRequest(new FindRequest, $request);
        $request['available'] = $request->input('available', null);
        $request['user_id'] = $request->input('user_id', null);
        $request['user_pid'] = $request->input('user_pid', null);
        $product = $this->ProductRepo->find($request->all(), $id);
        if (!$product) {
            return response()->json(['error' => 'Unable to find an product with an id of ' . $id], 404);
        }
        return response()->json($product, 200);
    }

    public function findBySlug(Request $request, $slug)
    {
        $this->validateRequest(new FindRequest, $request);
        $request['available'] = $request->input('available', null);
        $request['user_id'] = $request->input('user_id', null);
        $request['user_pid'] = $request->input('user_pid', null);
        $product = $this->ProductRepo->findBySlug($request->all(), $slug);
        if (!$product) {
            return response()->json(['error' => 'Unable to find product'], 404);
        }
        return response()->json($product, 200);
    }

    public function create(Request $request)
    {
        $this->validateRequest(new CreateRequest, $request);
        // Append user_pid if needed to allow old api calls to ommit user_pid
        if (!$request->has('user_pid')) {
            $request['user_pid'] = (new \App\Services\User\UserService)->getPidForId($request->input('user_id'));
        }
        $product = $this->ProductRepo->updateOrCreate($request->all());
        return response()->json($product, 200);
    }

    public function update(Request $request, $id)
    {
        $this->validateRequest(new UpdateRequest, $request, $id);
        $product = $this->ProductRepo->updateOrCreate($request->all(), $id);
        return response()->json($product, 200);
    }

    public function delete(Request $request, $id)
    {
        $this->validateRequest(new DeleteRequest, $request, $id);
        $product = $this->ProductRepo->delete($id);
        if ($product == false) {
            return response()->json(['error' => 'Unable to delete product with available inventory.'], 422);
        }
        return response()->json('Success', 200);
    }
}
