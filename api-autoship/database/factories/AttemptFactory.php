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

$factory->define(App\Models\Attempt::class, function (Faker\Generator $faker) {
    $faker = \Faker\Factory::create();
    $statuses = [];
    // Status of success 75% of the time
    for ($i = 0; $i < 75; $i++) {
        $statuses[] = 'success';
    }
    // Status of failure 22% of the time
    for ($i = 0; $i < 22; $i++) {
        $statuses[] = 'failure';
    }
    // Status of error 3% of the time
    for ($i = 0; $i < 3; $i++) {
        $statuses[] = 'error';
    }
    $status = $faker->randomElement($statuses);
    $description = [
        'success' => [
            'success'
        ],
        'failure' => [
            'Do not honor',
            'Insufficient Funds',
            'Invalid Card Number',
            'Expired Card',
        ],
        'error' => [
            'Trying to get property of non-object',
            'Undefined index',
            'Call to Undefined method'
        ]
    ];
    return [
        'description' => $faker->randomElement($description[$status]),
        'autoship_subscription_id' => 0,
        'order_pid' => null,
        'subscription_cycle'=> 0,
        'status' => $status,
    ];
});
