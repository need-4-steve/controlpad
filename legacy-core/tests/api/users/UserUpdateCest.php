<?php

namespace users;

use \ApiTester;
use \Step\Api\UserAuth;

class UserUpdateCest
{
    public function _before(UserAuth $I)
    {
    }

    public function _after(UserAuth $I)
    {
    }

    // tests
    public function tryToGetUserAsSuperAdmin(UserAuth $I)
    {
        $I->loginAsSuperadmin();
        $I->sendGET('/api/v1/user/show/106');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['id' => 106]);
    }

    public function tryToGetUserAsAdmin(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendGET('/api/v1/user/show/106');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['id' => 106]);
    }

    public function tryToGetUserAsRep(UserAuth $I)
    {
        $I->loginAsRep();
        $I->sendGET('/api/v1/user/show/106');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['id' => 106]);
    }
}
