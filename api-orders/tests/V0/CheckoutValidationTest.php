<?php namespace Test\V0;

use App\Cart;
use App\Cartline;
use Test\TestCase;

class CheckoutValidationTest extends TestCase
{

    public function createCheckoutValidation()
    {
        $path = '/api/v0/checkouts';
        // Assert minimum requirements
        $response = $this->basicRequest('POST', $path, []);
        $response->seeStatusCode(422);
        $response->seeJsonStructure([
            'subtotal',
            'type',
            'buyer_pid',
            'seller_pid',
            'inventory_user_pid',
            'lines',
            'billing_address',
            'shipping_address'
        ]);

        // Assert discount and shipping not negative
        $response = $this->basicRequest(
            'POST',
            $path,
            [
                'cart_pid' => 'somepid',
                'billing_address' => $address,
                'shipping_address' => $address,
                'shipping_is_billing' => false,
                'discount' => -1,
                'shipping' => -1
            ]
        );
        $response->seeStatusCode(422);
        $response->seeJsonStructure(['discount', 'shipping']);

        // Assert discount and shipping are number required
        $response = $this->basicRequest(
            'POST',
            $path,
            [
                'cart_pid' => 'somepid',
                'billing_address' => $address,
                'shipping_address' => $address,
                'shipping_is_billing' => false,
                'discount' => 'not a number',
                'shipping' => 'not a number'
            ]
        );
        $response->seeStatusCode(422);
        $response->seeJsonStructure(['discount', 'shipping']);
    }

    public function testCreateCartCheckoutvalidation()
    {
        $path = '/api/v0/carts/fake-id/create-checkout';
        $address = [
            'line_1' => '123 Main St',
            'city' => 'Orem',
            'state' => 'UT',
            'zip' => '84057'
        ];
        // Assert minimum requirements
        $response = $this->basicRequest('POST', $path, []);
        $response->seeStatusCode(422);
        $response->seeJsonStructure([
            'billing_address',
            'shipping_address'
        ]);

        // Assert filled requirements
        $response = $this->basicRequest(
            'POST',
            $path,
            [
                'cart_pid' => 'somepid',
                'billing_address' => [
                    'line_2' => 'Apt 1'
                ],
                'shipping_address' => [
                    'line_2' => 'Apt 1'
                ],
                'shipping_is_billing' => false
            ]
        );
        $response->seeStatusCode(422);
        $response->seeJsonStructure([
            'billing_address.zip',
            'shipping_address.line_1',
            'shipping_address.city',
            'shipping_address.state',
            'shipping_address.zip'
        ]);

        // Assert billing when shipping_is_billing is false
        $response = $this->basicRequest(
            'POST',
            $path,
            [
                'cart_pid' => 'somepid',
                'billing_address' => [
                    'line_2' => 'Apt 1'
                ],
                'shipping_address' => $address,
                'shipping_is_billing' => true
            ]
        );
        $response->seeStatusCode(422);
        $response->seeJsonStructure([
            'billing_address.line_1',
            'billing_address.city',
            'billing_address.state',
            'billing_address.zip'
        ]);
    }

    public function testCartEmptyValidation()
    {
        $cart = factory(Cart::class)->create();
        $response = $this->basicRequest(
            'POST',
            '/api/v0/carts/'.$cart->pid.'/create-checkout',
            [
                'cart_pid' => $cart->pid,
                'billing_address' => [
                    'line_1' => '123 Main St',
                    'city' => 'Orem',
                    'state' => 'UT',
                    'zip' => '84057'
                ],
                'shipping_is_billing' => true
            ]
        );
        $response->seeStatusCode(400);
        assert($response->response->content() === 'Cart empty');
    }

    public function testCreateCheckoutAuth()
    {
        $cart = factory(Cart::class)->create();
        $line = factory(Cartline::class)->create(['cart_id' => $cart->id]);
        $response = $this->basicRequest(
            'POST',
            '/api/v0/carts/'.$cart->pid.'/create-checkout',
            [
                'cart_pid' => $cart->pid,
                'billing_address' => [
                    'line_1' => '123 Main St',
                    'city' => 'Orem',
                    'state' => 'UT',
                    'zip' => '84057'
                ],
                'shipping_is_billing' => true
            ],
            'Customer',
            \CPCommon\Pid\Pid::Create(), // not the buyer
            999
        );
        $response->seeStatusCode(403);
        assert($response->response->content() === 'Buyer or admin only');
    }

    public function testCheckoutTypeValidation()
    {
        $cart = factory(Cart::class)->create();
        $line = factory(Cartline::class)->create(['cart_id' => $cart->id]);
        $response = $this->basicRequest(
            'POST',
            '/api/v0/carts/'.$cart->pid.'/create-checkout',
            [
                'cart_pid' => $cart->pid,
                'billing_address' => [
                    'line_1' => '123 Main St',
                    'city' => 'Orem',
                    'state' => 'UT',
                    'zip' => '84057'
                ],
                'shipping_is_billing' => true,
                'discount' => 1.23,
                'shipping' => 55.55
            ],
            'Customer',
            $cart->buyer_pid,
            null
        );
        $response->seeStatusCode(201);
        // Make sure default discount and shipping are returned
        $response->seeJson(['discount' => 0.00, 'shipping' => 10.00]);
    }
}
