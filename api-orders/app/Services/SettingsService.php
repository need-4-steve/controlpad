<?php

namespace App\Services;

class SettingsService implements SettingsServiceInterface
{

    private $companyPid;

    public function getSettings($keyList)
    {
        $settingsDirty = app('db')->table('settings')->select('key', 'value')->whereIn('key', $keyList)->get();
        if (!empty($settingsDirty)) {
            $settings = [];
            foreach ($settingsDirty as $key => $setting) {
                $settings[$setting->key] = json_decode($setting->value);
            }
            return (object)$settings;
        } else {
            return [];
        }
    }

    public function getCompanyPid()
    {
        if (!isset($this->companyPid)) {
            $this->companyPid = app('db')->table('users')->select('pid')->where('id', '=', 1)->first()->pid;
        }
        return $this->companyPid;
    }
}
