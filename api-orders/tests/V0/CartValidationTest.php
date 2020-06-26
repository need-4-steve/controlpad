<?php namespace Test\V0;

use App\Cart;
use App\Cartline;
use App\Coupon;
use Test\TestCase;
use Test\MockServices\MockInventoryService;
use Test\MockServices\MockSettingsService;

class CartValidationTest extends TestCase
{

    public function testGetCartsValidation()
    {
        // Index Param Validation
        $response = $this->basicRequest('GET', '/api/v0/carts');
        $response->seeStatusCode(422);
        $response->seeJsonStructure(
            [
                'page'
            ]
        );
    }

    public function testGetCartsAuth()
    {
        // Make sure carts filter for non admin
        factory(Cart::class)->create(['buyer_pid' => '100']);
        factory(Cart::class)->create(['buyer_pid' => '101']);
        factory(Cart::class, 2)->create(['buyer_pid' => '102']);
        $response = $this->basicRequest('GET', '/api/v0/carts', ['page' => 1], 'Customer', '102', 102);
        $response->seeStatusCode(200);
        $responseCarts = json_decode($response->response->content());
        foreach ($responseCarts as $cart) {
            assert($cart->buyer_pid === '102');
        }
    }

    public function testGetCartAuth()
    {
        // Make sure users can't see each others carts, response should be empty
        $cart = factory(Cart::class)->create(['buyer_pid' => '100']);
        $response = $this->basicRequest('GET', '/api/v0/carts', ['page' => 1], 'Customer', '106', 106);
        $response->seeStatusCode(200);
        $response->dontSeeJson(['pid' => $cart->pid]);
        assert(!$response->response->content());
    }

    public function testDeleteCartAuth()
    {
        // Make sure users can't delete each others carts
        $cart = factory(Cart::class)->create(['buyer_pid' => '100']);
        $response = $this->basicRequest('DELETE', '/api/v0/carts/'.$cart->pid, null, 'Customer', '106', 106);
        $response->seeStatusCode(403);
        $response->dontSeeJson(['pid' => $cart->pid]);
        $response->seeJson(['message' => 'Buyer or admin only']);
    }

    public function testCreateCartValidation()
    {
        // Check minimum requirements
        $response = $this->basicRequest('POST', '/api/v0/carts', []);
        $response->seeStatusCode(422);
        $response->seeJsonStructure([
            'seller_pid',
            'inventory_user_pid',
            'type'
        ]);

        // Check types
        $response = $this->basicRequest(
            'POST',
            '/api/v0/carts',
            [
                'buyer_pid' => 0,
                'type' => 'invalid',
                'seller_pid' => 0,
                'inventory_user_pid' => 0,
            ]
        );
        $response->seeStatusCode(422);
        $response->seeJsonStructure([
            'buyer_pid',
            'seller_pid',
            'inventory_user_pid',
            'type'
        ]);
    }

    public function testCreateCartAuth()
    {
        $path = '/api/v0/carts';
        $cart = [
            'buyer_pid' => '106',
            'seller_pid' => '1',
            'inventory_user_pid' => '1',
            'type' => 'wholesale'
        ];
        // Assert owner
        $response = $this->basicRequest('POST', $path, $cart, 'Customer', '100', 100);
        $response->seeStatusCode(403);

        // Assert wholesale buyer is a rep
        $response = $this->basicRequest(
            'POST',
            $path,
            ['buyer_pid' => '100', 'seller_pid' => '1', 'inventory_user_pid' => '1', 'type' => 'wholesale'],
            'Customer',
            100
        );
        $response->seeStatusCode(403);

        // Assert custom-corp is admin only
        $cart['type'] = 'custom-corp';
        $response = $this->basicRequest('POST', $path, $cart, 'Customer', '106', 106);
        $response->seeStatusCode(403);
        assert($response->response->content() == 'custom-corp type is admin only');

        // Assert custom-personal is owner
        $cart['type'] = 'custom-personal';
        $cart['seller_pid'] = '106';
        $cart['inventory_user_pid'] = '106';
        $response = $this->basicRequest('POST', $path, $cart, 'Rep', '100', 100);
        $response->seeStatusCode(403);
        assert($response->response->content() == 'custom-personal cart can only be operated by seller or admin');

        // Assert custom-personal is rep only
        $response = $this->basicRequest('POST', $path, $cart, 'Customer', '106', 106);
        $response->seeStatusCode(403);
        assert($response->response->content() == 'custom-personal cart can only be operated by rep or admin');

        // Assert custom-retail is owner
        $cart['type'] = 'custom-retail';
        $response = $this->basicRequest('POST', $path, $cart, 'Customer', '100', 100);
        $response->seeStatusCode(403);
        assert($response->response->content() == 'custom-retail cart can only be operated by seller or admin');
    }

    public function testCreateCartTypeValidation()
    {
        $path = '/api/v0/carts';
        // Assert custom-corp seller_pid must be 1
        $response = $this->basicRequest(
            'POST',
            $path,
            ['seller_pid' => '106', 'inventory_user_pid' => '1', 'type' => 'custom-corp']
        );
        $response->seeStatusCode(422);
        $response->seeJsonStructure(['type']);
        // Assert custom-corp inventory_user_pid must be 1
        $response = $this->basicRequest(
            'POST',
            $path,
            ['seller_pid' => '1', 'inventory_user_pid' => '106', 'type' => 'custom-corp']
        );
        $response->seeStatusCode(422);
        $response->seeJsonStructure(['type']);
        // Assert wholesale seller_pid must be 1
        $response = $this->basicRequest(
            'POST',
            $path,
            ['seller_pid' => '106', 'inventory_user_pid' => '1', 'type' => 'wholesale'],
            'Rep',
            '106',
            106
        );
        $response->seeStatusCode(422);
        $response->seeJsonStructure(['type']);
        // Assert wholesale inventory_user_pid must be 1
        $response = $this->basicRequest(
            'POST',
            $path,
            ['seller_pid' => '1', 'inventory_user_pid' => '106', 'type' => 'wholesale'],
            'Rep',
            '106',
            106
        );
        $response->seeStatusCode(422);
        $response->seeJsonStructure(['type']);
        // Assert affiliate inventory and seller are different
        $response = $this->basicRequest(
            'POST',
            $path,
            ['seller_pid' => '1', 'inventory_user_pid' => '1', 'type' => 'affiliate'],
            'Customer',
            null,
            null
        );
        $response->seeStatusCode(422);
        $response->seeJsonStructure(['type']);
        // Assert affiliate inventory_user_pid must be 1
        $response = $this->basicRequest(
            'POST',
            $path,
            ['seller_pid' => '108', 'inventory_user_pid' => '106', 'type' => 'affiliate'],
            'Customer',
            null,
            null
        );
        $response->seeStatusCode(422);
        $response->seeJsonStructure(['type']);
        // Assert custom-personal must be same seller/inventory
        $response = $this->basicRequest(
            'POST',
            $path,
            ['seller_pid' => '108', 'inventory_user_pid' => '106', 'type' => 'custom-personal'],
            'Rep',
            '108',
            108
        );
        $response->seeStatusCode(422);
        $response->seeJsonStructure(['type']);
        // Assert custom-retail must be same seller/inventory
        $response = $this->basicRequest(
            'POST',
            $path,
            ['seller_pid' => '108', 'inventory_user_pid' => '106', 'type' => 'custom-retail'],
            'Rep',
            '108',
            108
        );
        $response->seeStatusCode(422);
        $response->seeJsonStructure(['type']);
        // Assert retail must be same seller/inventory
        $response = $this->basicRequest(
            'POST',
            $path,
            ['seller_pid' => '108', 'inventory_user_pid' => '106', 'type' => 'retail'],
            'Customer',
            null,
            null
        );
        $response->seeStatusCode(422);
        $response->seeJsonStructure(['type']);
    }

    public function testBundleLineRestiction()
    {
        // Bundles should only be on wholesale
        $types = ['retail','custom-personal','custom-corp','custom-retail','affiliate'];
        $lines = [
            ['bundle_id' => 1, 'quantity' => 1]
        ];
        foreach ($types as $type) {
            $cart = factory(Cart::class)->create(['type' => $type]);
            $response = $this->basicRequest('POST', '/api/v0/carts/'.$cart->pid.'/lines', $lines);
            $response->seeStatusCode(400);
            assert($response->response->content() == 'Bundles only allowed with wholesale');
        }
    }

    public function testAddLinesValidation()
    {
        $cart = factory(Cart::class)->create(['type' => 'retail']);
        $path = '/api/v0/carts/'.$cart->pid.'/lines';
        // Check not empty body
        $response = $this->basicRequest('POST', $path, []);
        $response->seeStatusCode(400);
        // Check minimum requirements
        $response = $this->basicRequest('POST', $path, [[]]);
        $response->seeStatusCode(422);
        $response->seeJsonStructure(['0.item_id', '0.bundle_id', '0.quantity']);
        // Check item_id is int
        $response = $this->basicRequest('POST', $path, [['item_id' => 'string', 'quantity' => 1]]);
        $response->seeStatusCode(422);
        $response->seeJsonStructure(['0.item_id']);
        // Check bundle id is int
        $response = $this->basicRequest('POST', $path, [['bundle_id' => 'string', 'quantity' => 1]]);
        $response->seeStatusCode(422);
        $response->seeJsonStructure(['0.bundle_id']);
        // Check price is positive
        $response = $this->basicRequest('POST', $path, [['item_id' => 1, 'quantity' => 1, 'price' => -1.33]]);
        $response->seeStatusCode(422);
        $response->seeJsonStructure(['0.price']);
        // Check price is number
        $response = $this->basicRequest('POST', $path, [['item_id' => 1, 'quantity' => 1, 'price' => 'string']]);
        $response->seeStatusCode(422);
        $response->seeJsonStructure(['0.price']);
        // Check quantity greater than 0
        $response = $this->basicRequest('POST', $path, [['item_id' => 1, 'quantity' => 0]]);
        $response->seeStatusCode(422);
        $response->seeJsonStructure(['0.quantity']);
        // Check discount is positive
        $response = $this->basicRequest('POST', $path, [['item_id' => 1, 'quantity' => 1, 'discount' => -1.33]]);
        $response->seeStatusCode(422);
        $response->seeJsonStructure(['0.discount']);
        // Check price is number
        $response = $this->basicRequest('POST', $path, [['item_id' => 1, 'quantity' => 1, 'discount' => 'string']]);
        $response->seeStatusCode(422);
        $response->seeJsonStructure(['0.discount']);
        // Check discount type id is int
        $response = $this->basicRequest('POST', $path, [['item_id' => 1, 'quantity' => 1, 'discount_type_id' => 'string']]);
        $response->seeStatusCode(422);
        $response->seeJsonStructure(['0.discount_type_id']);
    }

    public function testApplyCouponValidation()
    {
        $cartCoupon = factory(Coupon::class)->create();
        $cart = factory(Cart::class)->create(['buyer_pid' => '100', 'coupon_id' => $cartCoupon->id]);
        $coupon = factory(Coupon::class)->create();
        $path = '/api/v0/carts/' . $cart->pid . '/apply-coupon';
        // Assert that code is required
        $response = $this->basicRequest('POST', $path, []);
        $response->seeStatusCode(422);
        $response->seeJsonStructure(['code']);
        // Assert buyer or admin
        $response = $this->basicRequest('POST', $path, ['code' => $coupon->code], 'Customer', '106', 106);
        $response->seeStatusCode(403);
        assert($response->response->content() === 'Buyer or admin only');
    }

    public function testApplyCouponCustomPersonal()
    {
        $cart = factory(Cart::class)->create(['type' => 'custom-personal']);
        $coupon = factory(Coupon::class)->create(['type' => 'retail']);

        $response = $this->basicRequest('POST', '/api/v0/carts/'.$cart->pid.'/apply-coupon', ['code' => $coupon->code]);
        $response->seeStatusCode(422);
        $response->seeJson(['code' => ['Cannot apply coupons to this order']]);
    }

    public function testApplyCouponWholesale()
    {
        $cart = factory(Cart::class)->create(['type' => 'wholesale']);
        $coupon = factory(Coupon::class)->create(['type' => 'retail']);

        $response = $this->basicRequest('POST', '/api/v0/carts/'.$cart->pid.'/apply-coupon', ['code' => $coupon->code]);
        $response->seeStatusCode(422);
        $response->seeJson(['code' => ['Wholesale coupons only']]);
    }

    public function testApplyCouponRetail()
    {
        $cart = factory(Cart::class)->create(['type' => 'retail']);
        $coupon =factory(Coupon::class)->create(['type' => 'wholesale']);

        $response = $this->basicRequest('POST', '/api/v0/carts/'.$cart->pid.'/apply-coupon', ['code' => $coupon->code]);
        $response->seeStatusCode(422);
        $response->seeJson(['code' => ['Retail coupons only']]);
    }

    public function testApplyCouponAffiliate()
    {
        $cart = factory(Cart::class)->create(['type' => 'affiliate']);
        $coupon =factory(Coupon::class)->create(['type' => 'wholesale']);

        $response = $this->basicRequest('POST', '/api/v0/carts/'.$cart->pid.'/apply-coupon', ['code' => $coupon->code]);
        $response->seeStatusCode(422);
        $response->seeJson(['code' => ['Retail coupons only']]);
    }

    public function testApplyCouponCustomCorp()
    {
        $cart = factory(Cart::class)->create(['type' => 'custom-corp']);
        $coupon =factory(Coupon::class)->create(['type' => 'wholesale']);

        $response = $this->basicRequest('POST', '/api/v0/carts/'.$cart->pid.'/apply-coupon', ['code' => $coupon->code]);
        $response->seeStatusCode(422);
        $response->seeJson(['code' => ['Retail coupons only']]);
    }

    public function testApplyCouponCustomRetail()
    {
        $cart = factory(Cart::class)->create(['type' => 'custom-retail']);
        $coupon =factory(Coupon::class)->create(['type' => 'wholesale']);

        $response = $this->basicRequest('POST', '/api/v0/carts/'.$cart->pid.'/apply-coupon', ['code' => $coupon->code]);
        $response->seeStatusCode(422);
        $response->seeJson(['code' => ['Retail coupons only']]);
    }
}
