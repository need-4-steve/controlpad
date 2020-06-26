<?php

use App\Models\Address;
use App\Models\User;

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

$factory->define(Address::class, function (Faker\Generator $faker) {

    $user = User::all()->random(1);
    $label = [
        'Billing',
        'Shipping',
        'Business'
    ];

    return [
        'name' => $user->first_name.' '.$user->last_name,
        'address_1' => $faker->streetAddress,
        'address_2' => $faker->secondaryAddress,
        'city' => $faker->city,
        'state' => strtoupper($faker->stateAbbr),
        'addressable_id' => $user->id,
        'addressable_type' => User::class,
        'zip' => $faker->postcode,
        'label' => $label[rand(0, 2)],
    ];
});

// Keep as a constant real address to get tax rates.
$factory->defineAs(Address::class, 'shipping', function ($faker) {
    return [
        'name' => $faker->name,
        'address_1' => '1411 W 1250 S',
        'address_2' => '',
        'city' => 'Orem',
        'state' => 'UT',
        'zip' => 84058,
        'label' => 'Shipping',
    ];
});

$factory->defineAs(Address::class, 'billing', function ($faker) {
    return [
        'name' => $faker->name,
        'address_1' => '1411 W 1250 S',
        'address_2' => '',
        'city' => 'Orem',
        'state' => 'UT',
        'zip' => 84058,
        'label' => 'Billing',
    ];
});
