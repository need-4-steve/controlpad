<?php namespace Test\V0;

use App\Cart;
use App\Cartline;
use App\Coupon;
use App\Checkout;
use App\Repositories\EloquentV0\CartRepository;
use Test\TestCase;
use Test\MockServices\MockInventoryService;
use Test\MockServices\MockSettingsService;
use Test\MockServices\MockTaxService;
use Test\MockServices\MockShippingService;
use CPCommon\Pid\Pid;

class CheckoutTest extends TestCase
{

    private $checkoutJsonStructure = [
        'pid',
        'seller_pid',
        'inventory_user_pid',
        'type',
        'total',
        'subtotal',
        'discount',
        'tax',
        'shipping',
        'tax_invoice_pid',
        'shipping_rate_id',
        'billing_address' => [
            'line_1',
            'city',
            'state',
            'zip',
            'email'
        ],
        'shipping_address' => [
            'line_1',
            'city',
            'state',
            'zip'
        ],
        'shipping_is_billing',
        'lines' => [
            '*' => [
                'orderline_pid',
                'item_id',
                'quantity',
                'price',
                'inventory_owner_pid',
            ]
        ],
        'coupon_id',
        'created_at',
        'updated_at'
    ];

    public function testGetCheckout()
    {
        $checkout = factory(Checkout::class)->create();

        $response = $this->basicRequest('GET', '/api/v0/checkouts/'.$checkout->pid);
        $response->seeStatusCode(200);
        $response->seeJsonStructure($this->checkoutJsonStructure);
        $response->seeJson($checkout->toArray());
    }

    public function testProcessManualCheckout()
    {
        MockSettingsService::setSetting('tax_calculation', ['show' => true, 'value' => true]);
        MockSettingsService::setSetting('tax_exempt_wholesale', ['show' => true, 'value' => true]);
        MockSettingsService::setSetting('wholesale_cart_min', ['show' => true, 'value' => 'dollar']);
        MockSettingsService::setSetting('wholesale_cart_min_amount', ['show' => false, 'value' => 0]);

        unset($this->checkoutJsonStructure['shipping_address']);
        $cartline = factory(Cartline::class)->make();
        $checkoutRequest = [
            'subtotal' => 10.00,
            'type' => 'retail',
            'buyer_pid' => 'buyer-pid',
            'seller_pid' => 'company-pid',
            'inventory_user_pid' => 'company-pid',
            'lines' => [
                $cartline->toArray()
            ],
            'billing_address' => [
                'email' => 'buyer@example.com',
                'line_1' => '123 Main St',
                'city' => 'Orem',
                'state' => 'UT',
                'zip' => '84057'
            ],
            'shipping_is_billing' => true
        ];

        $response = $this->basicRequest('POST', '/api/v0/checkouts', $checkoutRequest);
        $response->seeStatusCode(201);
        $response->seeJsonStructure($this->checkoutJsonStructure);
    }

    private function createCartCheckoutRequest($checkout, $buyerPid = '106', $sellerPid = '1', $cartType = 'wholesale')
    {
        $coupon = factory(Coupon::class)->create(['owner_pid' => $sellerPid]);
        $cart = factory(Cart::class)->create(['seller_pid' => $sellerPid, 'inventory_user_pid' => $sellerPid, 'type' => $cartType, 'coupon_id' => $coupon->id]);
        $line = factory(Cartline::class)->create(['cart_id' => $cart->id]);

        $checkout['cart_pid'] = $cart->pid;

        $response = $this->basicRequest('POST', '/api/v0/carts/'.$cart->pid.'/create-checkout', $checkout, 'Superadmin', $buyerPid);
        $response->seeStatusCode(201);
        $response->seeJsonStructure($this->checkoutJsonStructure);
        $response->seeJson($checkout);

        // Checkout lines set cartline_pid from pid
        $line->cartline_pid = $line->pid;
        unset($line->pid);
        unset($line->cart_id);

        $response->seeJson([
            'cart_pid' => $cart->pid,
            'seller_pid' => $cart->seller_pid,
            'inventory_user_pid' => $cart->inventory_user_pid,
            'type' => $cart->type
        ]);
        $response->seeJson($line->toArray());
        if (!isset($checkout['discount'])) {
            $response->seeJson(['coupon_id' => $coupon->id]);
        }
        return $response;
    }

    public function testCreateCheckout()
    {
        MockSettingsService::setSetting('tax_calculation', ['show' => true, 'value' => true]);
        MockSettingsService::setSetting('tax_exempt_wholesale', ['show' => true, 'value' => true]);
        MockSettingsService::setSetting('wholesale_cart_min', ['show' => true, 'value' => 'dollar']);
        MockSettingsService::setSetting('wholesale_cart_min_amount', ['show' => false, 'value' => 0]);

        $checkout = [
            'billing_address' => [
                'line_1' => '123 Main St',
                'line_2' => 'Apt 1',
                'city' => 'Orem',
                'state' => 'UT',
                'zip' => '84057',
                'email' => 'test@controlpad.com'
            ],
            'shipping_address' => [
                'line_1' => '199 Main St',
                'city' => 'Provo',
                'state' => 'UT',
                'zip' => '84604'
            ]
        ];

        $response = $this->createCartCheckoutRequest($checkout);
    }

    public function testCheckoutShippingIsBilling()
    {
        MockSettingsService::setSetting('tax_calculation', ['show' => true, 'value' => true]);
        MockSettingsService::setSetting('tax_exempt_wholesale', ['show' => true, 'value' => true]);
        MockSettingsService::setSetting('wholesale_cart_min', ['show' => true, 'value' => 'dollar']);
        MockSettingsService::setSetting('wholesale_cart_min_amount', ['show' => false, 'value' => 0]);
        unset($this->checkoutJsonStructure['shipping_address']);

        $checkout = [
            'billing_address' => [
                'line_1' => '123 Main St',
                'line_2' => 'Apt 1',
                'city' => 'Orem',
                'state' => 'UT',
                'zip' => '84057',
                'email' => 'test@controlpad.com'
            ],
            'shipping_is_billing' => true
        ];

        $response = $this->createCartCheckoutRequest($checkout);
    }

    public function testCreateCheckoutTax()
    {
        MockSettingsService::setSetting('tax_calculation', ['show' => true, 'value' => true]);
        MockSettingsService::setSetting('tax_exempt_wholesale', ['show' => true, 'value' => true]);
        MockSettingsService::setSetting('wholesale_cart_min', ['show' => true, 'value' => 'dollar']);
        MockSettingsService::setSetting('wholesale_cart_min_amount', ['show' => false, 'value' => 0]);

        $checkout = [
            'billing_address' => [
                'line_1' => '123 Main St',
                'line_2' => 'Apt 1',
                'city' => 'Orem',
                'state' => 'UT',
                'zip' => '84057',
                'email' => 'test@controlpad.com'
            ],
            'shipping_address' => [
                'line_1' => '199 Main St',
                'city' => 'Provo',
                'state' => 'UT',
                'zip' => '84604'
            ],
            'shipping_is_billing' => false
        ];

        $response = $this->createCartCheckoutRequest($checkout, '200', '106');
        $checkoutResponse = json_decode($response->response->content());
        assert(isset($checkoutResponse->tax_invoice_pid));
    }

    public function testCreateCheckoutCustomShippingAndDiscount()
    {
        MockSettingsService::setSetting('tax_calculation', ['show' => true, 'value' => true]);
        MockSettingsService::setSetting('tax_exempt_wholesale', ['show' => true, 'value' => true]);
        MockSettingsService::setSetting('wholesale_cart_min', ['show' => true, 'value' => 'dollar']);
        MockSettingsService::setSetting('wholesale_cart_min_amount', ['show' => false, 'value' => 0]);

        $checkout = [
            'billing_address' => [
                'line_1' => '123 Main St',
                'line_2' => 'Apt 1',
                'city' => 'Orem',
                'state' => 'UT',
                'zip' => '84057',
                'email' => 'test@controlpad.com'
            ],
            'shipping_address' => [
                'line_1' => '199 Main St',
                'city' => 'Provo',
                'state' => 'UT',
                'zip' => '84604'
            ],
            'shipping_is_billing' => false,
            'discount' => 1.00,
            'shipping' => 3.33
        ];

        $response = $this->createCartCheckoutRequest($checkout, '106', '106', 'custom-retail');
    }

    public function testCheckoutProcess()
    {
        $coupon = factory(Coupon::class)->create();
        $cart = factory(Cart::class)->create();

        $checkout = factory(Checkout::class)->create([
            'cart_pid' => $cart->pid,
            'coupon_id' => $coupon->id,
        ]);

        $processRequestBody = [
            'buyer' => [
                'email' => 'buyer@example.com',
                'first_name' => 'First',
                'last_name' => 'Last',
            ],
            'payment' => [
                'type' => 'card',
                'amount' => $checkout->total,
                'card' => [
                    'number' => '4111111111111111',
                    'year' => 2100,
                    'month' => 1,
                    'code' => '555'
                ]
            ]
        ];
        $path = '/api/v0/checkouts/' . $checkout->pid . '/process';
        $response = $this->basicRequest(
            'POST',
            $path,
            $processRequestBody,
            'Customer',
            $checkout->buyer_pid
        );
        $response->seeStatusCode(201);
        $response->seeJsonStructure([
            'order' => [
                'pid',
                'receipt_id',
                'confirmation_code',
                'customer_id',
                'buyer_first_name',
                'buyer_last_name',
                'buyer_email',
                'store_owner_user_id',
                'seller_name',
                'type',
                'transaction_id',
                'gateway_reference_id',
                'total_price',
                'subtotal_price',
                'total_discount',
                'total_tax',
                'total_shipping',
                'tax_invoice_pid',
                'shipping_rate_id',
                'paid_at',
                'cash',
                'source',
                'deleted_at',
                'comm_engine_status_id',
                'tax_not_charged',
                'lines' => [

                ],
                'coupon_id',
            ]
        ]);
        // TODO validate data created for the order
        // TODO Validate that coupon was updated
        // TODO Validate that cart lines were removed
    }
}
