<?php

namespace App\Repositories\Eloquent;

use DB;

class RepLocatorRepository
{
    public function searchUsersByZipCode($zipCodes)
    {
        $users = DB::table('users')
                    ->join('addresses', 'users.id', 'addresses.addressable_id')
                    ->join('roles', 'users.role_id', '=', 'roles.id')
                    ->join('user_setting', 'users.id', '=', 'user_setting.user_id')
                    ->join('user_status', 'users.status', '=', 'user_status.name')
                    ->join('geo_locations', 'addresses.id', '=', 'geo_locations.address_id', 'LEFT OUTER')
                    ->where('addressable_type', 'App\Models\User')
                    ->where('addresses.label', 'Business')
                    ->where('user_setting.show_location', 1)
                    ->where('user_status.rep_locator', 1)
                    ->where('roles.name', 'rep')
                    ->whereNull('users.deleted_at')
                    ->whereIn('addresses.zip', $zipCodes)
                    ->select('users.first_name', 'users.last_name', 'users.public_id', 'users.deleted_at', 'roles.name as role_name', 'user_setting.show_location as show_location', 'geo_locations.longitude', 'geo_locations.latitude', 'addresses.zip', 'addresses.label', 'addressable_type')
                    ->orderBy('users.first_name', 'asc')
                    ->get();
        return $users;
    }

    public function searchUsersByName($name, $limit = 10)
    {
        $users = DB::table('users')
                    ->join('store_settings', 'users.id', '=', 'store_settings.user_id')
                    ->join('roles', 'users.role_id', '=', 'roles.id')
                    ->join('user_setting', 'users.id', '=', 'user_setting.user_id')
                    ->join('addresses', 'users.id', '=', 'addresses.addressable_id', 'LEFT OUTER')
                    ->join('user_status', 'users.status', '=', 'user_status.name')
                    ->join('geo_locations', 'addresses.id', '=', 'geo_locations.address_id', 'LEFT OUTER')
                    ->where('addressable_type', 'App\Models\User')
                    ->where('addresses.label', 'Business')
                    ->where('user_setting.show_location', 1)
                    ->where('roles.name', 'rep')
                    ->where('store_settings.key', 'display_name')
                    ->where('user_status.rep_locator', 1)
                    ->whereNull('users.deleted_at')
                    ->where(function ($query) use ($name) {
                        $query->where('users.first_name', 'like', "%".$name."%")
                        ->orWhere('users.last_name', 'like', "%".$name."%")
                        ->orWhere(DB::raw("CONCAT(`first_name`, ' ', `last_name`)"), 'like', "%".$name."%")
                        ->orWhere('users.public_id', 'like', "%".$name."%")
                        ->orWhere('store_settings.value', 'like', "%".$name."%");
                    })
                    ->select('users.first_name', 'users.last_name', 'users.public_id', 'store_settings.value', 'geo_locations.longitude', 'geo_locations.latitude')
                    ->groupBy('users.id')
                    ->take($limit)
                    ->get();
        return $users;
    }

    public function getRandomUsers($limit = 10)
    {
        $users = DB::table('users')
                    ->select('users.first_name', 'users.last_name', 'users.public_id', 'geo_locations.longitude', 'geo_locations.latitude')
                    ->join('roles', 'users.role_id', '=', 'roles.id')
                    ->join('user_setting', 'users.id', '=', 'user_setting.user_id')
                    ->join('user_status', 'users.status', '=', 'user_status.name')
                    ->join('addresses', 'users.id', '=', 'addresses.addressable_id', 'LEFT OUTER')
                    ->join('geo_locations', 'addresses.id', '=', 'geo_locations.address_id', 'LEFT OUTER')
                    ->where('addressable_type', 'App\Models\User')
                    ->where('addresses.label', 'Business')
                    ->where('user_setting.show_location', 1)
                    ->where('roles.name', 'rep')
                    ->where('user_status.rep_locator', 1)
                    ->whereNull('users.deleted_at')
                    ->orderBy(DB::raw('RAND()'))
                    ->take($limit)
                    ->get();
        return $users;
    }
}
