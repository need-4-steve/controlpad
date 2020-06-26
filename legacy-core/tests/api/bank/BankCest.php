<?php
namespace bank;

use \ApiTester;
use \Step\Api\UserAuth;

class BankCest
{
    // tests
    public function tryToGetShowRep(UserAuth $I)
    {
        $I->loginAsRep();
        $I->wantTo('Show bank info for rep');
        $I->sendAjaxRequest('GET', '/api/v1/bank/show/106');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            "type" => "checking"
        ]);
    }

    public function tryToGetShowAdmin(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->wantTo('Show bank info for admin');
        $I->sendAjaxRequest('GET', '/api/v1/bank/show/106');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            "type" => "checking"
        ]);
    }

    public function tryToUpdateOrCreateAdmin(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->wantTo('Create or update banking for Admin');
        $I->sendAjaxRequest('PUT', '/api/v1/bank/update', [
            "name"          => "Account Name",
            "routing"       => "324377516",
            "number"        => "123456789",
            "type"          => "checking",
            "bankName"      => "Some Bank",
            "authorization" => true
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson();
    }

    public function tryToUpdateOrCreateRep(UserAuth $I)
    {
        $I->loginAsRep();
        $I->wantTo('Create or update banking for Rep');
        $I->sendAjaxRequest('PUT', '/api/v1/bank/update', [
            "name"          => "Account Name",
            "routing"       => "324377516",
            "number"        => "123456789",
            "type"          => "checking",
            "bankName"      => "Some Bank",
            "authorization" => true
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson();
    }
}
