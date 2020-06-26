<?php

namespace App\Http\Controllers\V0;

use App\Http\Controllers\Controller;
use App\Repositories\EloquentV0\SettingRepository;
use App\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    protected $SettingRepo;

    public function __construct()
    {
        $this->SettingRepo = new SettingRepository;
    }

    public function index(Request $request, $userPid)
    {
        $settings = $this->SettingRepo->index($userPid);
        return response()->json($settings, 200);
    }

    public function find(Request $request, $userPid, $settingKey)
    {
        $setting = $this->SettingRepo->find($userPid, $settingKey);
        if (is_null($setting)) {
            return response()->json(['error' => 'Unable to find Setting'], 404);
        }
        return response()->json($setting, 200);
    }

    public function update(Request $request, $userPid)
    {
        $this->validate($request, Setting::$updateRules);
        $setting = $this->SettingRepo->update($request->only(array_keys(Setting::$updateRules)), $userPid);
        return response()->json($setting, 200);
    }
}
