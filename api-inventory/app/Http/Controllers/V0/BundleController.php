<?php

namespace App\Http\Controllers\V0;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Repositories\EloquentV0\BundleRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Requests\FormRequest;
use App\Http\Requests\Bundles\CreateRequest;
use App\Http\Requests\Bundles\DeleteRequest;
use App\Http\Requests\Bundles\FindRequest;
use App\Http\Requests\Bundles\IndexRequest;
use App\Http\Requests\Bundles\UpdateRequest;

class BundleController extends Controller
{
    protected $BundleRepo;

    public function __construct()
    {
        $this->BundleRepo = new BundleRepository;
    }

    public function index(Request $request)
    {
        $this->validateRequest(new IndexRequest, $request);
        $request['available'] = $request->input('available', null);
        $request['per_page'] = $request->input('per_page', 100);
        $request['sort_by'] = $request->input('sort_by', 'name');
        $request['expands'] = $request->input('expands', []);
        $request['user_id'] = $request->input('user_id', null);
        $request['user_pid'] = $request->input('user_pid', null);
        $bundles = $this->BundleRepo->index($request->all());
        return response()->json($bundles, 200);
    }

    public function find(Request $request, $id)
    {
        $this->validateRequest(new FindRequest, $request);
        $request['available'] = $request->input('available', null);
        $request['user_id'] = $request->input('user_id', null);
        $request['user_pid'] = $request->input('user_pid', null);
        $bundle = $this->BundleRepo->find($request->all(), $id);
        if (!$bundle) {
            return response()->json(['error' => 'Unable to find an bundle with an id of ' . $id], 404);
        }
        return response()->json($bundle, 200);
    }

    public function create(Request $request)
    {
        $this->validateRequest(new CreateRequest, $request);
        // Append user_pid if needed to allow old api calls to ommit user_pid
        if (!$request->has('user_pid')) {
            $request['user_pid'] = (new \App\Services\User\UserService)->getPidForId($request->input('user_id'));
        }
        $bundle = $this->BundleRepo->updateOrCreate($request->all());
        return response()->json($bundle, 200);
    }

    public function update(Request $request, $id)
    {
        $this->validateRequest(new UpdateRequest, $request, $id);
        $bundle = $this->BundleRepo->updateOrCreate($request->all(), $id);
        return response()->json($bundle, 200);
    }

    public function delete(Request $request, $id)
    {
        $this->validateRequest(new DeleteRequest, $request, $id);
        $bundle = $this->BundleRepo->delete($id);
        if ($bundle == false) {
            return response()->json(['error' => 'Unable to delete.'], 422);
        }
        return response()->json('Success', 200);
    }
}
