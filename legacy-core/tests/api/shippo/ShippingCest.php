<?php

namespace shippo;

use ApiTester;
use App\Models\Order;
use Shippo;
use Step\Api\UserAuth;

class ShippingCest
{
    public function _before(UserAuth $I)
    {
        $this->request = [
            'address_from' => [
                'object_purpose' => 'PURCHASE',
                'name' => 'Rep',
                'street1' => '553 East Technology Ave Building C',
                'street2' => 'Ste 1300',
                'city' => 'Orem',
                'state' => 'UT',
                'zip' => '84097',
                'country' => 'US',
                'phone' => '800-830-4493',
                'email' => 'rep@controlpad.com'
            ],
            'address_to' => [
                'object_purpose' => 'PURCHASE',
                'name' => 'Customer',
                'street1' => '553 East Technology Ave Building C',
                'street2' => 'Ste 1300',
                'city' => 'Orem',
                'state' => 'UT',
                'zip' => '84097',
                'country' => 'US',
                'phone' => '800-830-4493',
                'email' => 'customer@controlpad.com'
            ],
            'parcel' => [
                'length'=> '5',
                'width'=> '5',
                'height'=> '5',
                'distance_unit'=> 'in',
                'weight'=> '2',
                'mass_unit'=> 'lb',
            ]
        ];
        $this->payment = [
            'card_number' => 4111111111111111,
            'security' => 555,
            'month' => '02',
            'year' => '2020',
            'name' => 'Code Ception',
            'address_1' => '553 East Technology Ave Building C',
            'address_2' => 'Ste 1300',
            'city' => 'Orem',
            'state' => 'UT',
            'zip' => 84097,
        ];
    }

    public function _after(UserAuth $I)
    {
    }

    public function tryToGetShipping(UserAuth $I)
    {
        $I->loginAsRep();

        // Get Shipping Rates
        $rateRequest = $this->request;
        $I->sendAjaxRequest('POST', '/api/v1/shipping/rates', $rateRequest);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'test' => true
        ]);

        $rates = json_decode($I->grabResponse());
        $rateId = null;
        foreach ($rates as $rate) {
            if ($rate->servicelevel->token === 'usps_priority') {
                $selectedRate = $rate;
                $rateId = $rate->object_id;
            }
        }
        if ($rateId === null) {
            $selectedRate = $rates[0];
            $rateId = $rates[0]->object_id;
        }
        $order = Order::where('store_owner_user_id', REP_ID)->first();

        // Get Shipping for the first rate returned
        $shippingRequest = [
            'order_id' => $order->id,
            'rate_id' => $rateId,
            'payment' => $this->payment
        ];
        $I->sendAjaxRequest('POST', '/api/v1/shipping', $shippingRequest);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'order_id' => $order->id,
            'rate' => $rateId,
            'total_price' => $selectedRate->total_price
        ]);
    }

    public function tryToGetShippingWithoutOrder(UserAuth $I)
    {
        $I->loginAsRep();
        // Get Shipping Rates
        $rateRequest = $this->request;
        $I->sendAjaxRequest('POST', '/api/v1/shipping/rates', $rateRequest);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'test' => true
        ]);

        $rates = json_decode($I->grabResponse());
        $rateId = null;
        foreach ($rates as $rate) {
            if ($rate->servicelevel->token === 'usps_priority') {
                $selectedRate = $rate;
                $rateId = $rate->object_id;
            }
        }
        if ($rateId === null) {
            $selectedRate = $rates[0];
            $rateId = $rates[0]->object_id;
        }

        // Get Shipping for the first rate returned
        $shippingRequest = [
            'rate_id' => $rateId,
            'payment' => $this->payment
        ];
        $I->sendAjaxRequest('POST', '/api/v1/shipping', $shippingRequest);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'order_id' => null,
            'rate' => $rateId,
            'total_price' => $selectedRate->total_price
        ]);
    }
}
