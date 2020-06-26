<?php

namespace App\Http\Controllers\V0;

use App\Http\Controllers\Controller;
use App\Repositories\EloquentV0\StoreSettingRepository;
use App\StoreSetting;
use Illuminate\Http\Request;

class StoreSettingController extends Controller
{
    protected $StoreSettingRepo;

    public function __construct()
    {
        $this->StoreSettingRepo = new StoreSettingRepository;
    }

    public function index(Request $request, $userPid)
    {
        $storeSettings = $this->StoreSettingRepo->index($userPid);
        return response()->json($storeSettings, 200);
    }

    public function find(Request $request, $userPid, $settingKey)
    {
        $storeSetting = $this->StoreSettingRepo->find($userPid, $settingKey);
        if (!$storeSetting) {
            return response()->json(['error' => 'Unable to find StoreSetting'], 404);
        }
        return response()->json($storeSetting, 200);
    }

    public function update(Request $request, $userPid)
    {
        $this->validate($request, StoreSetting::$updateRules);
        $storeSetting = $this->StoreSettingRepo->update($request->only(array_keys(StoreSetting::$updateRules)), $userPid);
        return response()->json($storeSetting, 200);
    }
}
