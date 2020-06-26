<?php namespace Test\V0;

use App\Cart;
use App\Cartline;
use App\Coupon;
use Test\TestCase;
use Test\MockServices\MockInventoryService;
use Test\MockServices\MockSettingsService;

class CartTest extends TestCase
{
    private $cartIndexJsonStructure = [
        'current_page',
        'data',
        'data' => [
            '*' => [
                'pid',
                'seller_pid',
                'buyer_pid',
                'inventory_user_pid',
                'type',
                'coupon_id',
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

    private $singleCartJsonStructure = [
        'pid',
        'seller_pid',
        'buyer_pid',
        'inventory_user_pid',
        'coupon_id',
        "type",
        "created_at",
        'updated_at'
    ];

    private $cartLinesJsonStructure = [
        '*' => [
            'pid',
            'item_id',
            'bundle_id',
            'bundle_name',
            'quantity',
            'price',
            'tax_class',
            'items' => [
                '*' => [
                    'id',
                    'inventory_id',
                    'product_name',
                    'variant_name',
                    'option_label',
                    'option',
                    'sku'
                ]
            ],
            'inventory_owner_pid',
        ]
    ];


    public function testGetCarts()
    {
        // TODO create carts that should filter different and check all filters
        $carts = factory(Cart::class, 8)->create();
        foreach ($carts as $cart) {
            $cart->lines;
            $cart->coupon;
        }
        $response = $this->basicRequest('GET', '/api/v0/carts', ['page' => 1]);
        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->cartIndexJsonStructure);
        $response->seeJson(['from' => 1, 'to' => 8, 'current_page' => 1]);
        $response->seeJson(['data' => $carts->toArray()]);
    }

    public function testGetCart()
    {
        $cart = factory(Cart::class)->create();
        // Make sure admin can access cart
        $response = $this->basicRequest('GET', '/api/v0/carts/'.$cart->pid);
        $response->seeStatusCode(200);
        $response->seeJson($cart->toArray());
        $response->seeJsonStructure($this->singleCartJsonStructure);

        // Make sure buyer can get cart
        $response = $this->basicRequest('GET', '/api/v0/carts/'.$cart->pid, null, 'Customer', $cart['buyer_pid'], 106);
        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->singleCartJsonStructure);
        $response->seeJson($cart->toArray());
    }

    public function testCreateCart()
    {
        $requestCart = ['buyer_pid' => '55', 'seller_pid' => '1',
            'inventory_user_pid' => '1', 'type' => 'wholesale'];
        $response = $this->basicRequest(
            'POST',
            '/api/v0/carts',
            $requestCart,
            'Rep',
            '55'
        );
        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->singleCartJsonStructure);
        $responseCart = json_decode($response->response->content(), 1);
        assert($responseCart['buyer_pid'] == $requestCart['buyer_pid']);
        assert($responseCart['seller_pid'] == $requestCart['seller_pid']);
        assert($responseCart['inventory_user_pid'] == $requestCart['inventory_user_pid']);
        assert($responseCart['type'] == $requestCart['type']);
        $response->seeInDatabase('carts', $responseCart);
    }

    public function testDeleteCart()
    {
        $cart = factory(Cart::class)->create();
        $cartline = factory(Cartline::class)->create(['cart_id' => $cart->id, 'quantity' => 5]);

        $response = $this->basicRequest(
            'DELETE',
            '/api/v0/carts/' . $cart->pid
        );
        $response->seeStatusCode(200);
        assert(empty($response->response->content()));
        assert(Cart::where('pid', $cart->pid)->first() == null);
        assert(Cartline::where('pid', $cartline->pid)->first() == null);
    }

    public function testAddWholesaleLines()
    {
        $existingItemId = MockInventoryService::NO_MIN_MAX_ITEM['id'];
        $lines = [
            ['item_id' => $existingItemId, 'quantity' => 3],
            ['bundle_id' => MockInventoryService::BUNDLE['id'], 'quantity' => 1]
        ];
        $cart = factory(Cart::class)->create();
        $cartLine = factory(Cartline::class)->create(
            [
                'cart_id' => $cart->id,
                'item_id' => $existingItemId,
                'quantity' => 1,
                'price' => 2.22,
                'inventory_owner_pid' => '1',
                'items' => [
                    [
                        'id' => 2,
                        'inventory_id' => 2,
                        'option' => 'M',
                        'option_label' => 'Size',
                        'premium_shipping_cost' => null,
                        'product_name' => 'No Min Max Product',
                        'sku' => '222222222',
                        'variant_name' => 'Variant Name',
                    ]
                ]
            ]
        );
        $cartLine->quantity = 4;
        $response = $this->basicRequest(
            'POST',
            '/api/v0/carts/' . $cart->pid . '/lines',
            $lines
        );
        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->cartLinesJsonStructure);
        $response->seeJson(['price' => MockInventoryService::NO_MIN_MAX_ITEM['wholesale_price']]);
        $response->seeJson(['price' => MockInventoryService::BUNDLE['wholesale_price']]);
        $response->seeJson($cartLine->toArray());
        $responseLines = json_decode($response->response->content(), 1);
        foreach ($responseLines as $line) {
            $line['items'] = json_encode($line['items']);
            $response->seeInDatabase('cartlines', $line);
        }
    }

    public function testAddCorpRetailLines()
    {
        $cart = factory(Cart::class)->create(['type' => 'retail']);
        $response = $this->basicRequest(
            'POST',
            '/api/v0/carts/' . $cart->pid . '/lines',
            [['item_id' => MockInventoryService::NO_MIN_MAX_ITEM['id'], 'quantity' => 2]],
            'Customer',
            $cart->buyer_pid
        );
        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->cartLinesJsonStructure);
        $response->seeJson(['price' => MockInventoryService::NO_MIN_MAX_ITEM['premium_price']]);
        $responseLines = json_decode($response->response->content(), 1);
        foreach ($responseLines as $line) {
            $line['items'] = json_encode($line['items']);
            $response->seeInDatabase('cartlines', $line);
        }
    }

    public function testAddRepRetailLines()
    {
        // Using a rep that has custom price enabled
        MockSettingsService::setSetting('rep_custom_price', [
            'show' => true,
            'value' => 'rep prices'
        ]);
        $lines = [
            [
                'item_id' => MockInventoryService::NO_MIN_MAX_ITEM['id'],
                'quantity' => 1
            ],
            [
                'item_id' => MockInventoryService::NO_INV_PRICE_ITEM['id'],
                'quantity' => 1
            ]
        ];
        $cart = factory(Cart::class)->create(['type' => 'retail', 'seller_pid' => '106', 'inventory_user_pid' => '106']);
        $response = $this->basicRequest(
            'POST',
            '/api/v0/carts/' . $cart->pid . '/lines',
            $lines
        );
        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->cartLinesJsonStructure);
        $response->seeJson(['price' => MockInventoryService::NO_MIN_MAX_ITEM['inventory_price']]);
        $response->seeJson(['price' => MockInventoryService::NO_INV_PRICE_ITEM['retail_price']]);
        $responseLines = json_decode($response->response->content(), 1);
        foreach ($responseLines as $line) {
            $line['items'] = json_encode($line['items']);
            $response->seeInDatabase('cartlines', $line);
        }
    }

    public function testAddAffiliateLines()
    {
        $cart = factory(Cart::class)->create(['type' => 'retail']);
        $response = $this->basicRequest(
            'POST',
            '/api/v0/carts/' . $cart->pid . '/lines',
            [['item_id' => MockInventoryService::NO_MIN_MAX_ITEM['id'], 'quantity' => 2]]
        );
        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->cartLinesJsonStructure);
        $response->seeJson(['price' => MockInventoryService::NO_MIN_MAX_ITEM['premium_price']]);
        $responseLines = json_decode($response->response->content(), 1);
        foreach ($responseLines as $line) {
            $line['items'] = json_encode($line['items']);
            $response->seeInDatabase('cartlines', $line);
        }
    }

    public function testAddCustomCorpLines()
    {
        $customPrice = 3.13;
        $lines = [
            [
                'item_id' => MockInventoryService::NO_MIN_MAX_ITEM['id'],
                'quantity' => 2,
                'price' => $customPrice
            ],
            [
                'item_id' => MockInventoryService::NO_INV_PRICE_ITEM['id'],
                'quantity' => 4
            ]
        ];
        $cart = factory(Cart::class)->create(['type' => 'custom-corp']);
        $response = $this->basicRequest(
            'POST',
            '/api/v0/carts/' . $cart->pid . '/lines',
            $lines
        );
        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->cartLinesJsonStructure);
        $response->seeJson(['price' => $customPrice]);
        $response->seeJson(['price' => MockInventoryService::NO_INV_PRICE_ITEM['premium_price']]);
        $responseLines = json_decode($response->response->content(), 1);
        foreach ($responseLines as $line) {
            $line['items'] = json_encode($line['items']);
            $response->seeInDatabase('cartlines', $line);
        }
    }

    public function testAddCustomPersonalLines()
    {
        $customPrice = 3.13;
        $cart = factory(Cart::class)->create(['type' => 'custom-personal']);
        $response = $this->basicRequest(
            'POST',
            '/api/v0/carts/' . $cart->pid . '/lines',
            [['item_id' => MockInventoryService::NO_MIN_MAX_ITEM['id'], 'quantity' => 2]]
        );
        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->cartLinesJsonStructure);
        $response->seeJson(['price' => MockInventoryService::NO_MIN_MAX_ITEM['wholesale_price']]);
        $responseLines = json_decode($response->response->content(), 1);
        foreach ($responseLines as $line) {
            $line['items'] = json_encode($line['items']);
            $response->seeInDatabase('cartlines', $line);
        }
    }

    public function testApplyWholesaleCoupon()
    {
        $wholesaleCoupon = factory(Coupon::class)->create();
        $cart = factory(Cart::class)->create();
        $response = $this->basicRequest(
            'POST',
            '/api/v0/carts/' . $cart->pid . '/apply-coupon',
            ['code' => $wholesaleCoupon->code]
        );

        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->singleCartJsonStructure);
        $cart->coupon = $wholesaleCoupon;
        $cart->coupon_id = $wholesaleCoupon->id;
        $response->seeJson($cart->toArray());  // Resposne matches cart and applied coupons
    }

    public function testApplyRetailCoupon()
    {
        $retailCoupon = factory(Coupon::class)->create(['type' => 'retail']);
        $cart = factory(Cart::class)->create(['type' => 'retail']);
        $response = $this->basicRequest(
            'POST',
            '/api/v0/carts/' . $cart->pid . '/apply-coupon',
            ['code' => $retailCoupon->code]
        );

        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->singleCartJsonStructure);
        $cart->coupon = $retailCoupon;
        $cart->coupon_id = $retailCoupon->id;
        $response->seeJson($retailCoupon->toArray()); // Coupon is in response
        $response->seeJson($cart->toArray());  // Resposne matches cart and applied coupons
    }

    public function testApplyAffiliateCoupon()
    {
        $retailCoupon = factory(Coupon::class)->create(['type' => 'retail']);
        $cart = factory(Cart::class)->create(['type' => 'affiliate']);
        $response = $this->basicRequest(
            'POST',
            '/api/v0/carts/' . $cart->pid . '/apply-coupon',
            ['code' => $retailCoupon->code]
        );

        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->singleCartJsonStructure);
        $cart->coupon = $retailCoupon;
        $cart->coupon_id = $retailCoupon->id;
        $response->seeJson($retailCoupon->toArray()); // Coupon is in response
        $response->seeJson($cart->toArray());  // Resposne matches cart and applied coupons
    }

    public function testApplyCustomCorpCoupon()
    {
        $retailCoupon = factory(Coupon::class)->create(['type' => 'retail']);
        $cart = factory(Cart::class)->create(['type' => 'custom-corp']);
        $response = $this->basicRequest(
            'POST',
            '/api/v0/carts/' . $cart->pid . '/apply-coupon',
            ['code' => $retailCoupon->code]
        );

        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->singleCartJsonStructure);
        $cart->coupon = $retailCoupon;
        $cart->coupon_id = $retailCoupon->id;
        $response->seeJson($retailCoupon->toArray()); // Coupon is in response
        $response->seeJson($cart->toArray());  // Resposne matches cart and applied coupons
    }

    public function testApplyCustomRetailCoupon()
    {
        $retailCoupon = factory(Coupon::class)->create(['type' => 'retail']);
        $cart = factory(Cart::class)->create(['type' => 'custom-retail']);
        $response = $this->basicRequest(
            'POST',
            '/api/v0/carts/' . $cart->pid . '/apply-coupon',
            ['code' => $retailCoupon->code]
        );

        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->singleCartJsonStructure);
        $cart->coupon = $retailCoupon;
        $cart->coupon_id = $retailCoupon->id;
        $response->seeJson($retailCoupon->toArray()); // Coupon is in response
        $response->seeJson($cart->toArray());  // Resposne matches cart and applied coupons
    }

    public function testPatchCartline()
    {
        $cart = factory(Cart::class)->create();
        $cartline = factory(Cartline::class)->create(['cart_id' => $cart->id, 'quantity' => 5]);

        $response = $this->basicRequest(
            'PATCH',
            '/api/v0/cartlines/' . $cartline->pid,
            ['quantity' => 3]
        );
        $cartlineArray = $cartline->toArray();
        $cartlineArray['quantity'] = 3;
        $response->seeStatusCode(200);
        $response->seeJson($cartlineArray);
        $cartlineArray['items'] = json_encode($cartlineArray['items']);
        $response->seeInDatabase('cartlines', $cartlineArray);
    }

    public function testDeleteCartline()
    {
        $cart = factory(Cart::class)->create();
        $cartline = factory(Cartline::class)->create(['cart_id' => $cart->id, 'quantity' => 5]);

        $response = $this->basicRequest(
            'DELETE',
            '/api/v0/cartlines/' . $cartline->pid
        );
        $response->seeStatusCode(200);
        assert(empty($response->response->content()));
        assert(Cartline::where('pid', $cartline->pid)->first() == null);
    }
}
