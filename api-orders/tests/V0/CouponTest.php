<?php namespace Test\V0;

use App\Coupon;
use Carbon\Carbon;
use Test\TestCase;

class CouponTest extends TestCase
{
    private $couponIndexJsonStructure = [
        'current_page',
        'data',
        'data' => [
            '*' => [
                'owner_pid',
                'code',
                'amount',
                'is_percent',
                'title',
                'description',
                'type',
                'uses',
                'max_uses',
                'created_at',
                'updated_at'
            ]
        ],
        'first_page_url',
        'next_page_url',
        'prev_page_url',
        'path',
        'from',
        'to'
    ];

    private $singleCouponJsonStructure = [
        'owner_pid',
        'code',
        'amount',
        'is_percent',
        'title',
        'description',
        'type',
        'uses',
        'max_uses',
        "created_at",
        'updated_at'
    ];

    public function testGetCoupons()
    {
        $path = '/api/v0/coupons';
        $expiredCoupon = factory(Coupon::class)->create(['expires_at' => Carbon::yesterday()->toDateTimeString()]);
        $usedCoupon = factory(Coupon::class)->create(['uses' => 3, 'max_uses' => 3, 'title' => 'Used Coupon']);
        $activeCoupon = factory(Coupon::class)->create(['amount' => 22.22, 'description' => 'Twenty percent off', 'max_uses' => 100]);
        $repCoupon = factory(Coupon::class)->create(['owner_pid' => '106', 'type' => 'retail']);

        // status active
        $response = $this->basicRequest('GET', $path, ['status' => 'active', 'page' => 1]);
        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->couponIndexJsonStructure);
        $response->seeJson($activeCoupon->toArray());
        $response->seeJson(['to' => 1]);
        // status used
        $response = $this->basicRequest('GET', $path, ['status' => 'used', 'page' => 1]);
        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->couponIndexJsonStructure);
        $response->seeJson($usedCoupon->toArray());
        $response->seeJson(['to' => 1]);
        // status expired
        $response = $this->basicRequest('GET', $path, ['status' => 'expired', 'page' => 1]);
        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->couponIndexJsonStructure);
        $response->seeJson($expiredCoupon->toArray());
        $response->seeJson(['to' => 1]);
        // rep coupon
        $response = $this->basicRequest('GET', $path, ['page' => 1], 'Rep', '106');
        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->couponIndexJsonStructure);
        $response->seeJson($repCoupon->toArray());
        $response->seeJson(['to' => 1]);
        // search_term code
        $response = $this->basicRequest('GET', $path, ['search_term' => $activeCoupon->code, 'page' => 1]);
        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->couponIndexJsonStructure);
        $response->seeJson($activeCoupon->toArray());
        $response->seeJson(['to' => 1]);
        // search_term title
        $response = $this->basicRequest('GET', $path, ['search_term' => 'used', 'page' => 1]);
        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->couponIndexJsonStructure);
        $response->seeJson($usedCoupon->toArray());
        $response->seeJson(['to' => 1]);
        // search_term amoun
        $response = $this->basicRequest('GET', $path, ['search_term' => '22.22', 'page' => 1]);
        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->couponIndexJsonStructure);
        $response->seeJson($activeCoupon->toArray());
        $response->seeJson(['to' => 1]);
        // search_term description
        $response = $this->basicRequest('GET', $path, ['search_term' => 'Twenty', 'page' => 1]);
        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->couponIndexJsonStructure);
        $response->seeJson($activeCoupon->toArray());
        $response->seeJson(['to' => 1]);
        // search_term max_uses
        $response = $this->basicRequest('GET', $path, ['search_term' => '100', 'page' => 1]);
        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->couponIndexJsonStructure);
        $response->seeJson($activeCoupon->toArray());
        $response->seeJson(['to' => 1]);
        // wholesale coupons
        $response = $this->basicRequest('GET', $path, ['type' => 'wholesale', 'page' => 1]);
        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->couponIndexJsonStructure);
        $response->seeJson($activeCoupon->toArray());
        $response->seeJson($expiredCoupon->toArray());
        $response->seeJson($usedCoupon->toArray());
        $response->seeJson(['to' => 3]);
    }

    // TODO test sort coupons

    public function testIndexPaginate()
    {
        factory(Coupon::class, 20)->create();
        $response = $this->basicRequest('GET', '/api/v0/coupons', ['page' => 2, 'per_page' => 15]);
        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->couponIndexJsonStructure);
        $response->seeJson(['from' => 16, 'to' => 20]);
    }

    public function testShowCoupon()
    {
        $coupon = factory(Coupon::class)->create();
        $response = $this->basicRequest('GET', '/api/v0/coupons/' . $coupon->id);
        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->singleCouponJsonStructure);
        $response->seeJson($coupon->toArray());
    }

    public function testCreateCoupon()
    {
        $coupon = [
            'code' => 'bigthirty',
            'owner_pid' => '1',
            'amount' => 30,
            'is_percent' => true,
            'title' => 'The Big Thirty',
            'description' => 'Thirty percent off.',
            'type' => 'wholesale',
            'max_uses' => 100,
            'expires_at' => Carbon::now()->setTimezone('UTC')->addYears(1)->toDateTimeString()
        ];
        $response = $this->basicRequest('POST', '/api/v0/coupons', $coupon);
        $response->seeStatusCode(201);
        $response->seeJsonStructure($this->singleCouponJsonStructure);
        $response->seeJson($coupon);
        $response->seeInDatabase('coupons', json_decode($response->response->content(), 1));
    }

    public function testDeleteCoupon()
    {
        $coupon = factory(Coupon::class)->create();
        $path = '/api/v0/coupons/' . $coupon->id;
        $response = $this->basicRequest('DELETE', $path);
        $response->seeStatusCode(200);

        // Make sure coupon is deleted
        assert(!Coupon::where('id', $coupon->id)->exists());
        $response = $this->basicRequest('GET', $path);
        $response->seeStatusCode(200);
        assert(!$response->response->content());
    }
}
