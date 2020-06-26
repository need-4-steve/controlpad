<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\AuthRepository;
use App\Repositories\Eloquent\StoreSettingRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Services\Store\RepStore;

class StoreSettingsController extends Controller
{
    protected $storeSettingRepo;
    protected $authRepo;

    /**
     * Create a new controller instance.
     *
     * @param AuthRepository  $authRepo
     * @param StoreSettingRepository $storeSettingRepo
     * @return void
     */
    public function __construct(
        AuthRepository $authRepo,
        StoreSettingRepository $storeSettingRepo,
        ProductRepository $productRepo
    ) {
        $this->authRepo = $authRepo;
        $this->storeSettingRepo = $storeSettingRepo;
        $this->productRepo = $productRepo;
    }

    /**
    * Updates or creates a new category header for a user.
    *
    * @return Response
    */
    public function updateCategoryHeader()
    {
        $request = request()->all();
        $category = $this->storeSettingRepo->updateCategoryHeader($this->authRepo->getOwner(), $request['id'], $request['header']);

        if (isset($category['error'])) {
            return response()->json($category['error'], 422);
        }

        return response()->json($category, 200);
    }

    /**
    * Updates or creates a new store setting for a user.
    *
    * @return Response
    */
    public function update()
    {
        $request = request()->all();
        $setting = $this->storeSettingRepo->update($this->authRepo->getOwner(), $request['key'], $request['value']);

        if (isset($setting['error'])) {
            return response()->json($setting['error'], 422);
        }
        $ownerId = $this->authRepo->getOwnerId();
        cache()->forget('store-settings-'.$ownerId);
        return response()->json($setting, 200);
    }

    /**
    * Gets the auth user store.
    *
    * @return Store
    */
    public function getStore()
    {
        $store = new RepStore(['limit' => 8], $this->authRepo->getOwner(), $this->productRepo);
        return response()->json($store, 200);
    }

    public function getUserStoreSettings($user_id)
    {
        $storeSettings = $this->storeSettingRepo->getSettingsByUser($user_id);
        return response()->json($storeSettings, 200);
    }
}
