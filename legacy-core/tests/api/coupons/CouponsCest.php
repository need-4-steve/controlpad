<?php

namespace coupons;

use App\Models\Coupon;
use App\Models\Order;
use \Step\Api\UserAuth;

class CouponsCest
{
    public function _before(UserAuth $I)
    {
        $this->taxRequest = [
            'billingAddress' => [
                'address_1' => '67 Some St',
                'city' => 'Orem',
                'zip' => 84058,
                'state' => 'UT'
            ],
            'shippingAddress' => [
                'address_1' => '67 Some St',
                'city' => 'Orem',
                'zip' => 84058,
                'state' => 'UT'
            ],
            'billingName' => 'Timmy',
            'shippingName' => 'Timmy',
            'products' => [
                [
                    ['item_id' => 96,
                    'price' => 13.41,
                    'quantity' => 1,
                    'item' => [
                        'custom_sku' => 19872137,
                        'height' => 10,
                        'id' => 96,
                        'length' => 22,
                        'print' => 'Blue',
                        'weight' => .5,
                        'width' => 3
                    ]]
                ]
            ]
        ];
    }

    public function _after(UserAuth $I)
    {
    }

    /*
     * Create a test coupon and persist it to the database
     */
    public function createTestCoupon($owner_id = null)
    {
        if ($owner_id == null) {
            $owner_id = config('site.apex_user_id');
        }

        $coupon = factory(Coupon::class)
                    ->create(['owner_id' => $owner_id,
                    'type' => 'wholesale'])
                    ->toArray();
        return $coupon;
    }

    /*
     * Create a test coupon and persist it to the database that is for retail
     */
    public function createTestCouponRetail($owner_id = null)
    {
        if ($owner_id == null) {
            $owner_id = config('site.apex_user_id');
        }

        $coupon = factory(Coupon::class)
                    ->create(['owner_id' => $owner_id,
                    'type' => 'retail'])
                    ->toArray();
        unset($coupon['created_at']);
        unset($coupon['updated_at']);
        return $coupon;
    }

    /*
     * Make a test coupon without persisting to database
     */
    public function makeTestCoupon($owner_id = null)
    {
        if ($owner_id == null) {
            $owner_id = config('site.apex_user_id');
        }

        return factory(Coupon::class)
                    ->make(['owner_id' => $owner_id])
                    ->toArray();
    }

    // tests
    public function tryToCreateCouponAsAdmin(UserAuth $I)
    {
        $I->wantTo("Create a new coupon as an Admin");
        $I->loginAsAdmin();
        $coupon = $this->makeTestCoupon();
        $I->sendAjaxPostRequest('/api/v1/coupons', $coupon);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson($coupon);
    }

    public function tryToCreateCouponWithRepUsedCode(UserAuth $I)
    {
        $I->loginAsAdmin();
        $coupon = $this->createTestCoupon(REP_ID);
        unset($coupon['id']);
        $coupon['amount'] = round($coupon['amount']);
        $I->sendAjaxPostRequest('/api/v1/coupons', $coupon);
        $I->seeResponseCodeIs(200);
        unset($coupon['id']);
        unset($coupon['created_at']);
        unset($coupon['updated_at']);
        $coupon['owner_id'] = config('site.apex_user_id');
        $I->seeResponseContainsJson($coupon);
        $I->seeRecord('coupons', $coupon);
    }

    public function tryToShowCouponAsAdmin(UserAuth $I)
    {
        $I->wantTo("Show an existing coupon as an Admin");
        $I->loginAsAdmin();
        $coupon = $this->createTestCoupon();
        $url = '/api/v1/coupons/' . $coupon['id'];
        $I->sendAjaxRequest('GET', $url);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson($coupon);
    }

    public function tryToDeleteCouponAsAdmin(UserAuth $I)
    {
        $I->wantTo("Delete an existing coupon as an Admin");
        $I->loginAsAdmin();
        $coupon = $this->createTestCoupon();
        $url = '/api/v1/coupons/' . $coupon['id'];
        $I->sendAjaxRequest('DELETE', $url);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['Success']);
    }

    public function tryToCreateCouponAsRep(UserAuth $I)
    {
        $I->wantTo("Create a new coupon as a rep");
        $I->loginAsRep();
        $coupon = $this->makeTestCoupon(REP_ID);
        $I->sendAjaxPostRequest('/api/v1/coupons', $coupon);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson($coupon);
    }

    public function tryToShowCouponAsRep(UserAuth $I)
    {
        $I->wantTo("Show an existing coupon as a rep");
        $I->loginAsRep();
        $coupon = $this->createTestCouponRetail();
        $url = '/api/v1/coupons/' . $coupon['id'];
        $I->sendAjaxRequest('GET', $url);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson($coupon);
    }

    public function tryToDeleteCouponAsRep(UserAuth $I)
    {
        $I->wantTo("Delete an existing coupon as a rep");
        $I->loginAsRep();
        $coupon = $this->createTestCoupon();
        $url = '/api/v1/coupons/' . $coupon['id'];
        $I->sendAjaxRequest('DELETE', $url);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['Success']);
    }

    public function tryToApplyCouponAsRep(UserAuth $I)
    {
        $I->wantTo("Apply a coupon to the cart as a Rep");
        $I->loginAsRep();
        $coupon = $this->createTestCoupon();
        $this->taxRequest['cart_type'] = 'wholesale';
        $I->sendAjaxPostRequest('/api/v1/coupons/apply/' . $coupon['code'], $this->taxRequest);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['coupons' => $coupon]);
    }

    public function tryToApplyCouponAsCustomer(UserAuth $I)
    {
        $I->wantTo("Apply a coupon to the cart");
        $coupon = $this->createTestCouponRetail();
        $this->taxRequest['cart_type'] = 'retail';
        $I->sendAjaxPostRequest('/api/v1/coupons/apply/' . $coupon['code'], $this->taxRequest);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['coupons' => $coupon]);
    }

    public function getshowCoupon(UserAuth $I)
    {
        $I->wantTo('see coupons with orders');
        $I->haveRecord('applied_coupons', [
            'coupon_id' => '1',
            'couponable_id' => '1',
            'couponable_type' => 'App/Models/Order',
        ]);
        $coupon = Coupon::first();
        Order::first()->update([
            'total_discount' => -$coupon->amount
        ]);
        $I->loginAsAdmin();
        $I->sendAjaxGetRequest('/api/v1/coupons/show-order/1');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
    }
}
