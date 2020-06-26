<?php
namespace api;

use \Step\Api\UserAuth;

class OrderStatusCest
{
    public function _before(UserAuth $I)
    {
    }

    public function _after(UserAuth $I)
    {
    }

    // tests
    public function tryGetOrderStatusIndex(UserAuth $I)
    {
        $I->loginAsRep();
        $I->sendGET('/api/v1/order-status');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([
            'name' => 'string',
            'position' => 'integer',
            'visible' => 'integer:<2',
            'default' => 'integer:<2'
        ]);
    }

    public function tryCreateOrderStatus(UserAuth $I)
    {
        $I->loginAsAdmin();
        $request = [
            'name' => strtolower(str_random(7)),
            'position' => rand(1, 100),
            'visible' => false
        ];
        $I->sendPOST('/api/v1/order-status', $request);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([
            'name' => 'string',
            'position' => 'integer',
            'visible' => 'boolean'
        ]);
        $I->seeResponseContainsJson($request);
    }

    public function tryUpdateOrderStatus(UserAuth $I)
    {
        $I->loginAsAdmin();
        $statusId = $I->haveRecord('order_status', [
            'name' => strtolower(str_random(7)),
            'position' => rand(1, 100),
            'visible' => false
        ]);
        $request = [
            'name' => strtolower(str_random(7)),
            'position' => rand(1, 100),
            'visible' => true
        ];
        $I->sendPATCH('/api/v1/order-status/'.$statusId, $request);
        $I->seeResponseCodeIs(200);
        $I->seeRecord('order_status', $request);
    }

    public function tryDeleteOrderStatus(UserAuth $I)
    {
        $I->loginAsAdmin();
        $statusId = $I->haveRecord('order_status', [
            'name' => strtolower(str_random(7)),
            'position' => rand(1, 100),
            'visible' => false
        ]);
        $I->sendDELETE('/api/v1/order-status/'.$statusId);
        $I->seeResponseCodeIs(200);
        $I->dontSeeRecord('order_status', ['id' => $statusId]);
    }
}
