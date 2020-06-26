<?php

// Do not use ->create() on this factory
$factory->defineAs(App\Models\Cart::class, 'paymentInfo', function ($faker) {
    return [
        'card_number' => '4111111111111111',
        'security' => rand(100, 999),
        'month' => rand(10, 12),
        'year' => date('Y') + 1,
        'name' => $faker->name
    ];
});