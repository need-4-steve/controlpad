<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\Cart::class, function (Faker\Generator $faker) {
    return [
        'pid' => CPCommon\Pid\Pid::create(),
        'seller_pid' => '1',
        'buyer_pid' => CPCommon\Pid\Pid::create(),
        'inventory_user_pid' => '1',
        'type' => 'wholesale',
        'coupon_id' => null,
    ];
});

$factory->define(App\Cartline::class, function (Faker\Generator $faker) {
    return [
        'cart_id' => 1, // Override
        'inventory_owner_pid' => CPCommon\Pid\Pid::create(),
        'item_id' => 1,
        'pid' => CPCommon\Pid\Pid::create(),
        'price' => 4.44,
        'quantity' => 1,
        'discount' => 0,
        'discount_type_id' => null,
        'bundle_id' => null,
        'event_id' => null,
        'items' => [
            [
                'id' => 1,
                'inventory_id' => 1,
                'option' => 'S',
                'option_label' => 'Size',
                'premium_shipping_cost' => null,
                'product_name' => 'Min Max Product',
                'sku' => '111111111',
                'variant_name' => 'Variant Name',
            ]
        ]
    ];
});

$factory->define(App\Coupon::class, function (Faker\Generator $faker) {
    return [
        'amount' => 5.00,
        'code' => CPCommon\Pid\Pid::create(),
        'created_at' => null,
        'deleted_at' => null,
        'description' => 'Coupon for testing',
        'expires_at' => null,
        'is_percent' => 1,
        'max_uses' => 8,
        'owner_pid' => '1',
        'title' => 'Test Coupon',
        'type' => 'wholesale',
        'updated_at' => null,
        'uses' => 0,
    ];
});

$factory->define(App\Checkout::class, function (Faker\Generator $faker) {
    return [
        'pid' => CPCommon\Pid\Pid::create(),
        'cart_pid' => CPCommon\Pid\Pid::create(),
        'seller_pid' => '1',
        'buyer_pid' => CPCommon\Pid\Pid::create(),
        'inventory_user_pid' => CPCommon\Pid\Pid::create(),
        'type' => 'wholesale',
        'total' => 7.01,
        'subtotal' => 4.44,
        'discount' => 0.00,
        'tax' => 0.27,
        'shipping' => 2.30,
        'billing_address' => [
            'city' => 'Orem',
            'email' => 'test@controlpad.com',
            'line_1' => '123 Main St',
            'line_2' => 'Apt 1',
            'state' => 'UT',
            'zip' => '84057'
        ],
        'shipping_address' => [
            'city' => 'Provo',
            'line_1' => '199 Main St',
            'state' => 'UT',
            'zip' => '84604'
        ],
        'shipping_is_billing' => false,
        'lines' => [
            [
                'cartline_pid' => CPCommon\Pid\Pid::create(),
                'inventory_owner_pid' => CPCommon\Pid\Pid::create(),
                'item_id' => 1,
                'orderline_pid' => CPCommon\Pid\Pid::create(),
                'premium_shipping_amount' => null,
                'price' => 4.44,
                'quantity' => 1
            ]
        ],
        'coupon_id' => null
    ];
});
