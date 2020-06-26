<?php
namespace settings;

use \ApiTester;
use \App\Models\Setting;
use \Step\Api\UserAuth;

class SettingCest
{
    public function _before(UserAuth $I)
    {
        $this->settings = Setting::first();
    }

    public function _after(UserAuth $I)
    {
    }

    public function tryToGetIndex(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendGET('/api/v1/settings/');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(json_decode($this->settings->value, true));
    }

    public function tryToShowUser(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendGET('/api/v1/settings/show/1');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            ]);
    }

    public function tryToUpdate(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendPOST('/api/v1/settings/update');
        $I->seeResponseCodeIs(200);
    }
}
