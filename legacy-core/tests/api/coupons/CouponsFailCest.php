<?php

namespace coupons;

use App\Models\Coupon;
use \Step\Api\UserAuth;

class CouponsFailCest
{
    public function _before(UserAuth $I)
    {
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

        return factory(Coupon::class)
                    ->create(['owner_id' => $owner_id])
                    ->toArray();
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
    public function tryToCreateCouponAsCustomer(UserAuth $I)
    {
        $I->wantTo("Create a new coupon as a customer");
        $coupon = $this->makeTestCoupon();
        $I->sendAjaxPostRequest('/api/v1/coupons', $coupon);
        $I->seeResponseCodeIs(401);
    }

    public function tryToShowCouponAsCustomer(UserAuth $I)
    {
        $I->wantTo("Show an existing coupon as a rep");
        $coupon = $this->createTestCoupon();
        $url = '/api/v1/coupons/' . $coupon['id'];
        $I->sendAjaxRequest('GET', $url);
        $I->seeResponseCodeIs(401);
    }

    public function tryToDeleteCouponAsCustomer(UserAuth $I)
    {
        $I->wantTo("Delete an existing coupon as a customer");
        $coupon = $this->createTestCoupon();
        $url = '/api/v1/coupons/' . $coupon['id'];
        $I->sendAjaxRequest('DELETE', $url);
        $I->seeResponseCodeIs(401);
    }

    public function tryToCreateCouponWithUsedCode(UserAuth $I)
    {
        $I->loginAsRep();
        $coupon = $this->createTestCoupon(REP_ID);
        $I->sendAjaxPostRequest('/api/v1/coupons', $coupon);
        $I->seeResponseCodeIs(422);
    }

    public function tryToSeeCoupon(UserAuth $I)
    {
        $I->sendAjaxGetRequest('/api/v1/coupons/show-order/1');
        $I->seeResponseCodeIs(401);
    }
}
