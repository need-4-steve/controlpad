<?php
namespace dashboard;

use \ApiTester;
use \Step\Api\UserAuth;

class DashboardCest
{
    public function _before(UserAuth $I)
    {
    }

    public function _after(ApiTester $I)
    {
    }

    // tests
    public function tryToGetSaleVolumeAdmin(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendAjaxRequest('GET', '/api/v1/dashboard/sales-volume');
        $I->seeResponseCodeIs(200);
    }

    public function tryToGetSaleVolumeRep(UserAuth $I)
    {
        $I->loginAsRep();
        $I->sendAjaxRequest('GET', '/api/v1/dashboard/sales-volume');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson();
    }
}
