<?php

use App\Address;

$factory->define(Address::class, function ($faker) {
    return [
        'name' => $faker->name,
        'address_1' => $faker->streetAddress,
        'address_2' => $faker->secondaryAddress,
        'city' => $faker->city,
        'state' => strtoupper($faker->stateAbbr),
        'zip' => $faker->postcode,
        'label' => 'Shipping',
        'addressable_type' => 'App\Models\User',
        'addressable_id' => 0
    ];
});

$factory->defineAs(Address::class, 'request', function ($faker) {
    return [
        'name' => $faker->name,
        'line_1' => $faker->streetAddress,
        'line_2' => $faker->secondaryAddress,
        'city' => $faker->city,
        'state' => strtoupper($faker->stateAbbr),
        'zip' => $faker->postcode,
    ];
});
