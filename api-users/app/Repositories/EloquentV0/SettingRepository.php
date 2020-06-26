<?php

namespace App\Repositories\EloquentV0;

use App\Setting;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use App\User;

class SettingRepository extends Repository
{
    public function create(User $user, String $timezone = 'UTC')
    {
        $setting = Setting::create([
            'user_id' => $user->id,
            'user_pid' => $user->pid,
            'timezone' => $timezone,
        ]);
        return $setting;
    }

    public function find($userPid, $key)
    {
        $setting = Setting::select($key)->where('user_pid', $userPid)->first();
        if (isset($setting->$key)) {
            return $setting->$key;
        }
        return null;
    }

    public function index($userPid)
    {
        $settings = Setting::where('user_pid', $userPid)->first();
        return $settings;
    }

    public function update($request, $userPid)
    {
        app('db')->beginTransaction();
        $update = Setting::where('user_pid', $userPid)->update($request);
        $settings = Setting::where('user_pid', $userPid)->first();
        app('db')->commit();
        return $settings;
    }
}
