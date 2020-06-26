<?php

use App\Models\Plan;
use CPCommon\Pid\Pid;

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

$factory->define(App\Models\Subscription::class, function (Faker\Generator $faker) {
    $sellerPid = Pid::create();
    return [
        'buyer_first_name'      => $faker->firstName,
        'buyer_last_name'       => $faker->lastName,
        'buyer_pid'             => Pid::create(),
        'discounts' => [
            ['min_quantity' => 1, 'percent' => 2],
            ['min_quantity' => 2, 'percent' => 3],
            ['min_quantity' => 3, 'percent' => 5],
            ['min_quantity' => 4, 'percent' => 7],
        ],
        'duration'              => 'Months',
        'free_shipping'         => 0,
        'frequency'             => 1,
        'next_billing_at'       => $faker->dateTimeBetween('+1 day', '+1 month')->format('Y-m-d H:i:s'),
        'seller_pid'            => $sellerPid,
        'inventory_user_pid'    => $sellerPid,
        'type'                  => 'wholesale',
    ];
});

$factory->defineAs(App\Models\Subscription::class, 'Seeder', function (Faker\Generator $faker) {
    $plan = Plan::inRandomOrder()->first();
    $sellerPid = Pid::create();
    return [
        'autoship_plan_id'      => $plan->id,
        'buyer_first_name'      => $faker->firstName,
        'buyer_last_name'       => $faker->lastName,
        'buyer_pid'             => Pid::create(),
        'discounts' => [
            ['min_quantity' => 1, 'percent' => 2],
            ['min_quantity' => 2, 'percent' => 3],
            ['min_quantity' => 3, 'percent' => 5],
            ['min_quantity' => 4, 'percent' => 7],
        ],
        'duration'              => 'Months',
        'free_shipping'         => 0,
        'frequency'             => 1,
        'next_billing_at'       => $faker->dateTimeBetween('-1 month', '-1 day')->format('Y-m-d H:i:s'),
        'seller_pid'            => $sellerPid,
        'inventory_user_pid'    => $sellerPid,
        'type'                  => 'wholesale',
    ];
});
