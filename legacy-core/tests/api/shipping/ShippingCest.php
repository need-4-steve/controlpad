<?php
namespace shipping;

use \ApiTester;
use \Step\Api\UserAuth;
use \App\Models\ShippingRate;

class ShippingCest
{
    public function _before(ApiTester $I)
    {
        $this->shipping = ShippingRate::where(['user_id' => 1, 'type' => 'retail'])->first();
    }

    public function _after(ApiTester $I)
    {
    }

    // tests
    public function tryToGetIndex(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendAjaxRequest('GET', '/api/v1/shipping-rate');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            [
            'user_id' => $this->shipping->user_id
            ]
            ]);
    }

    public function tryToGetCreate(UserAuth $I)
    {
        $I->loginAsAdmin();
        $user = (object)[
        'id' =>106];
        $inputs = [
            'ranges' => [
                [
                 "amount" => 29.99,
                  "max" => 20,
                  'name' => 'Standard Shipping',
                  'type' => 'retail'
                ],
                [ "amount" => 49.99,
                  "max" => 45,
                  'name' => 'Standard Shipping',
                  'type' => 'retail'
                ],
                [
                "amount" => 59.99,
                "max" => null,
                'name' => 'Standard Shipping',
                'type' => 'retail'
                ]
            ]
        ];
        $I->sendAjaxRequest('POST', '/api/v1/shipping-rate/create', $inputs, $user);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson();
    }

    public function tryToGetShippingCost(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendAjaxRequest('GET', '/api/v1/shipping-rate/shipping-cost', [106], [35]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'error' => false,
            'message' => 'Success']);
    }
}
