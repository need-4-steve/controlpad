<?php

namespace App\Services\Settings;

use App\Repositories\Eloquent\StoreSettingRepository;
use App\Repositories\Eloquent\AuthRepository;
use App\Models\Setting;
use App\Models\UserSetting;

class SettingsService
{
    protected $authRepo;

    public function __construct()
    {
        $this->authRepo = new AuthRepository;
        $this->storeSettingRepo = new StoreSettingRepository;
        $this->userSettings = $this->userSettings();
        $this->globalSettings = $this->globalSettings();
        $this->storeSettings = $this->storeSettings();
    }

    public function userSettings()
    {
        $settings = cache()->get('user-settings-'.auth()->id());
        if (!$settings) {
            $settings = UserSetting::where('user_id', $this->authRepo->getOwnerId())->first();
            cache()->forever('user-settings-'.auth()->id(), $settings);
        }
        return $settings;
    }

    public function globalSettings()
    {
        $settings = cache()->get('global-settings');
        if (!$settings) {
            $settings = Setting::where('user_id', config('site.apex_user_id'))->get();
            cache()->forever('global-settings', $settings);
        }
        return $settings;
    }

    // function to pull store settings
    public function storeSettings()
    {
        $pieces = explode('.', request()->getHost());
        $store_owner = \App\Models\User::where('public_id', $pieces[0])->first();
        if ($store_owner && $store_owner->role_id == 5) {
            $storeOwnerId = $store_owner->id;
            $settings = cache()->get('store-settings-'.$store_owner->id);
        } else {
            $storeOwnerId = $this->authRepo->getStoreOwnerId();
            $settings = cache()->get('store-settings-'.$storeOwnerId);
        }
        if (!$settings) {
            $settings = $this->storeSettingRepo->getSettingsByUser($storeOwnerId);
            cache()->forever('store-settings-'.$storeOwnerId, $settings);
        }
        return $settings;
    }

    /*
     * function to return if we are logged in as a rep user
     */
    public function isOwnerRep()
    {
        return $this->authRepo->isOwnerRep();
    }

    /*
     * function to return if we are logged in as owner
     */
    public function isOwnerAdmin()
    {
        return $this->authRepo->isOwnerAdmin();
    }

    public function getUser($setting)
    {
        // if no settings for user return null
        if (empty($this->userSettings)) {
            return null;
        }

        // setting found with value return that
        if (isset($this->userSettings->$setting)) {
            return $this->userSettings->$setting;
        }

        // settings are set but couldn't find this specific setting
        return null;
    }

    public function getGlobal($key, $value)
    {
        // if no settings for user return null
        if (empty($this->globalSettings)) {
            return null;
        }

        $setting = $this->globalSettings->where('key', $key)->first();

        // setting found with value return that
        if (isset($setting)) {
            $thisSetting = json_decode($setting->value, true);
            if (isset($thisSetting[$value])) {
                return $thisSetting[$value];
            }
        }

        // settings are set but couldn't find this specific setting
        return null;
    }

    // function to pull store settings
    public function getStore($key)
    {
        // if no settings for user return null
        if (empty($this->storeSettings)) {
            return null;
        }

        $position = array_search($key, $this->storeSettings);
        $setting = $this->storeSettings[$key];

        // setting found with value return that
        if (isset($setting)) {
            return $setting;
        }

        // settings are set but couldn't find this specific setting
        return null;
    }
}
