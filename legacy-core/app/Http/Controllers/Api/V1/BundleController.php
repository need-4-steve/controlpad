<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\BundleRepository;
use App\Repositories\Eloquent\RoleRepository;
use App\Repositories\Eloquent\AuthRepository;
use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\Role;
use App\Http\Requests\CreateBundleRequest;
use App\Http\Requests\EditBundleRequest;

class BundleController extends Controller
{
    protected $productRepo;
    protected $bundleRepo;
    protected $roleRepo;

    public function __construct(ProductRepository $productRepo, BundleRepository $bundleRepo, RoleRepository $roleRepo)
    {
        $this->productRepo = $productRepo;
        $this->bundleRepo = $bundleRepo;
        $this->roleRepo = $roleRepo;
    }

    public function create(CreateBundleRequest $request)
    {
        return $this->bundleRepo->create($request->all());
    }

    public function index()
    {
        $request = request()->all();
        $bundles = $this->bundleRepo->index($request);
        return response()->json($bundles, HTTP_SUCCESS);
    }

    public function bundlesByRoleFulfilled()
    {
        //TODO:: The date being returned need to be fixed.
        $queryStr = $this->getQueryStrs(request()->all());
        if (auth()->check()) {
            $role_id = auth()->user()->role->id;
        } else {
            $role_id = $this->roleRepo->findIdByName('Customer');
        }
        return $this->bundleRepo->getBundlesByRole($queryStr, $role_id, 2);
    }

    public function bundlesByRole()
    {
        //TODO:: The date being returned need to be fixed.
        $queryStr = $this->getQueryStrs(request()->all());
        if (auth()->check()) {
            $role_id = auth()->user()->role->id;
        } else {
            $role_id = $this->roleRepo->findIdByName('Customer');
        }
        return $this->bundleRepo->getBundlesByRole($queryStr, $role_id);
    }

    public function show($id)
    {
        $bundle = $this->bundleRepo->getBundleAsProducts($id);
        $bundle['bundle']->checkedRoles = $this->roleRepo->findCheckedRoles(
            $this->roleRepo->all(),
            $bundle['bundle']->roles
        );
        return response()->json($bundle, HTTP_SUCCESS);
    }

    public function edit($id, EditBundleRequest $request)
    {
        $bundle = $this->bundleRepo->find($id);
        return $this->bundleRepo->update($bundle, $request->all());
    }

    public function delete($id)
    {
        $authRepo = new AuthRepository;
        $userId = $authRepo->getOwnerId();
        $bundle = Bundle::where('user_id', $userId)->where('id', $id)->first();
        if (!empty($bundle)) {
            $bundle->items()->detach();
            $bundle->category()->detach();
            $bundle->media()->detach();
            $bundle->roles()->detach();
            $bundle->prices()->delete();
            $bundle->delete();
            return response()->json(['message' => 'Bundle deleted.'], 200);
        }
        return response()->json('Unauthorized', 401);
    }

    /*
     * Show all bundles that are a starter kit.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function starterKits()
    {
        $starterKits = $this->bundleRepo->starterKits();

        if (!$starterKits) {
            return response()->json('Could not find any starter kits.', HTTP_BAD_REQUEST);
        }

        return response()->json($starterKits);
    }

    private function getQueryStrs(array $request)
    {
        if ($request['category'] === 'all') {
            $request['category'] = null;
        }

        //set default query strings
        $queryStrs = [
            'searchTerm' => '',
            'sortBy' => 'name',
            'order' => 'ASC',
            'category' => null,
            'limit' => 25,
            'per_page' => 15
        ];
        //override default query strings with request
        foreach ($queryStrs as $key => $value) {
            if (array_key_exists($key, $request)) {
                $queryStrs[$key] = $request[$key];
            }
        }
        //set if desc requests
        if (isset($request['sortBy'])) {
            if ($request['sortBy'] === 'name_desc') {
                $queryStrs['sortBy'] = 'name';
                $queryStrs['order'] = 'DESC';
            } elseif ($request['sortBy'] === 'price_desc') {
                $queryStrs['sortBy'] = 'price';
                $queryStrs['order'] = 'DESC';
            }
        }
        return $queryStrs;
    }
}
