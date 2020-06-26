<?php namespace Test\V0;

use App\Coupon;
use Test\TestCase;

class CouponValidationTest extends TestCase
{
    public function testGetCouponsValidation()
    {
        $path = '/api/v0/coupons';
        $response = $this->basicRequest('GET', $path);
        $response->seeStatusCode(422);
        $response->seeJsonStructure(
            [
                'page'
            ]
        );
    }

    public function testGetCouponsAuth()
    {
        // Make sure coupons filter for non admin
        factory(Coupon::class)->create(['owner_pid' => '100']);
        factory(Coupon::class)->create(['owner_pid' => '101']);
        factory(Coupon::class, 2)->create(['owner_pid' => '102']);
        $response = $this->basicRequest('GET', '/api/v0/coupons', ['page' => 1], 'Rep', '102');
        $response->seeStatusCode(200);
        $responseCoupons = json_decode($response->response->content());
        foreach ($responseCoupons as $coupon) {
            assert($coupon->owner_pid == '102');
        }
        // Make sure user can't select another user
        $response = $this->basicRequest('GET', '/api/v0/coupons', ['page' => 1, 'owner_pid' => '101'], 'Rep', '102');
        $response->seeStatusCode(403);
    }

    public function testGetCouponAuth()
    {
        // Make sure users can't see each others coupons, response should be empty
        $coupon = factory(Coupon::class)->create(['owner_pid' => '100']);
        $response = $this->basicRequest('GET', '/api/v0/coupons/' . $coupon->id, null, 'Rep', '106');
        $response->seeStatusCode(200);
        assert(!$response->response->content()); // Make sure nothing returned
    }

    public function testCreateCouponValidation()
    {
        $path = '/api/v0/coupons';
        // Check minimum requirements
        $response = $this->basicRequest('POST', $path, []);
        $response->seeStatusCode(422);
        $response->seeJsonStructure(['code', 'amount', 'is_percent', 'title', 'max_uses']);
        // Check minimum amount and max_uses
        $response = $this->basicRequest('POST', $path, ['code' => 'fake', 'amount' => 0, 'max_uses' => 0]);
        $response->seeStatusCode(422);
        $response->seeJsonStructure(['amount', 'max_uses']);
    }

    public function testCreateCouponAuth()
    {
        $path = '/api/v0/coupons';
        $coupon = [
            'code' => 'fake',
            'amount' => 5,
            'is_percent' => true,
            'type' => 'retail',
            'owner_pid' => '100',
            'max_uses' => 1,
            'title' => 'Test Coupon'
        ];
        // Assert owner
        $response = $this->basicRequest('POST', $path, $coupon, 'Rep', 106);
        $response->seeStatusCode(403);
        // Assert wholesale coupon owner is admin
        $coupon['type'] = 'wholesale';
        $response = $this->basicRequest('POST', $path, $coupon, 'Rep', 100);
        $response->seeStatusCode(403);
    }
}
