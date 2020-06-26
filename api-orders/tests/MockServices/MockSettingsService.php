<?php

namespace Test\MockServices;

use DB;

class MockSettingsService implements \App\Services\SettingsServiceInterface
{

    private static $settings = [];

    public function getSettings($keyList)
    {
        return json_decode(json_encode(MockSettingsService::$settings));
    }

    public static function setSetting($key, $value)
    {
        MockSettingsService::$settings[$key] = json_decode(json_encode($value));
    }

    public function getCompanyPid()
    {
        return '1';
    }

    public static function clearSettings()
    {
        MockSettingsService::$settings = [];
    }
}
