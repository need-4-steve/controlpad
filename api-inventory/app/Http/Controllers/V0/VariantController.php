<?php

namespace App\Http\Controllers\V0;

use App\Http\Controllers\Controller;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Requests\FormRequest;
use App\Http\Requests\Variants\CreateRequest;
use App\Http\Requests\Variants\DeleteRequest;
use App\Http\Requests\Variants\FindRequest;
use App\Http\Requests\Variants\IndexRequest;
use App\Http\Requests\Variants\UpdateRequest;
use App\Repositories\EloquentV0\VariantRepository;

class VariantController extends Controller
{
    protected $VariantRepo;

    public function __construct()
    {
        $this->VariantRepo = new VariantRepository;
    }

    public function index(Request $request)
    {
        $this->validateRequest(new IndexRequest, $request);
        $request['available'] = $request->input('available', null);
        $request['per_page'] = $request->input('per_page', 100);
        $request['sort_by'] = $request->input('sort_by', 'name');
        $request['user_id'] = $request->input('user_id', null);
        $request['user_pid'] = $request->input('user_pid', null);
        $items = $this->VariantRepo->index($request->all());
        return response()->json($items, 200);
    }

    public function find(Request $request, $id)
    {
        $this->validateRequest(new FindRequest, $request);
        $request['user_id'] = $request->input('user_id', null);
        $request['user_pid'] = $request->input('user_pid', null);
        $request['available'] = $request->input('available', null);
        $variant = $this->VariantRepo->find($request->all(), $id);
        if (!$variant) {
            return response()->json(['error' => 'Unable to find a variant with an id of ' . $id], 404);
        }
        return response()->json($variant, 200);
    }

    public function create(Request $request)
    {
        $this->validateRequest(new CreateRequest, $request);
        $variant = $this->VariantRepo->updateOrcreate($request->all());
        return response()->json($variant, 200);
    }

    public function update(Request $request, $id)
    {
        $this->validateRequest(new UpdateRequest, $request, $id);
        $variant = $this->VariantRepo->updateOrcreate($request->all(), $id);
        return response()->json($variant, 200);
    }

    public function delete(Request $request, $id)
    {
        $this->validateRequest(new DeleteRequest, $request, $id);
        $variant = $this->VariantRepo->delete($id);
        if ($variant == false) {
            return response()->json(['error' => 'Unable to delete variant with available inventory.'], 422);
        }
        return response()->json('Success', 200);
    }
}
