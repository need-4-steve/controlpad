<?php
namespace api;

use \Step\Api\UserAuth;

class UserStatusCest
{
    public function _before(UserAuth $I)
    {
    }

    public function _after(UserAuth $I)
    {
    }

    // tests
    public function tryGetUserStatusIndex(UserAuth $I)
    {
        $I->loginAsRep();
        $I->sendGET('/api/v1/user-status');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([
            'active' => [
                'name' => 'string',
                'position' => 'integer',
                'visible' => 'integer:<2',
                'default' => 'integer:<2',
                'login' => 'integer:<2',
                'buy' => 'integer:<2',
                'sell' => 'integer:<2',
                'renew_subscription' => 'integer:<2',
                'rep_locator' => 'integer:<2',
            ]
        ]);
    }

    public function tryCreateUserStatus(UserAuth $I)
    {
        $I->loginAsAdmin();
        $request = [
            'name' => strtolower(str_random(7)),
            'position' => rand(1, 100),
            'visible' => false,
            'login' => false,
            'buy' => false,
            'sell' => false,
            'renew_subscription' => false,
            'rep_locator' => false,
        ];
        $I->sendPOST('/api/v1/user-status', $request);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([
            'name' => 'string',
            'position' => 'integer',
            'visible' => 'boolean',
            'login' => 'boolean',
            'buy' => 'boolean',
            'sell' => 'boolean',
            'renew_subscription' => 'boolean',
            'rep_locator' => 'boolean',
        ]);
        $I->seeResponseContainsJson($request);
    }

    public function tryUpdateUserStatus(UserAuth $I)
    {
        $I->loginAsAdmin();
        $statusId = $I->haveRecord('user_status', [
            'name' => strtolower(str_random(7)),
            'position' => rand(1, 100),
            'visible' => false,
            'login' => false,
            'buy' => false,
            'sell' => false,
            'renew_subscription' => false,
            'rep_locator' => false,
        ]);
        $request = [
            'name' => strtolower(str_random(7)),
            'position' => rand(1, 100),
            'visible' => false,
            'login' => false,
            'buy' => false,
            'sell' => false,
            'renew_subscription' => false,
            'rep_locator' => false,
        ];
        $I->sendPATCH('/api/v1/user-status/'.$statusId, $request);
        $I->seeResponseCodeIs(200);
        $I->seeRecord('user_status', $request);
    }

    public function tryDeleteUserStatus(UserAuth $I)
    {
        $I->loginAsAdmin();
        $statusId = $I->haveRecord('user_status', [
            'name' => strtolower(str_random(7)),
            'position' => rand(1, 100),
            'visible' => false,
            'login' => false,
            'buy' => false,
            'sell' => false,
            'renew_subscription' => false,
            'rep_locator' => false,
        ]);
        $I->sendDELETE('/api/v1/user-status/'.$statusId);
        $I->seeResponseCodeIs(200);
        $I->dontSeeRecord('user_status', ['id' => $statusId]);
    }
}
