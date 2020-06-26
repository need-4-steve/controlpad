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

$factory->define(App\Models\Plan::class, function (Faker\Generator $faker) {
    $durations = [
        'Days' => 30,
        'Weeks' => 4,
        'Months' => 1,
    ];
    $duration = array_rand($durations);
    return [
        'description'   => implode($faker->sentences(1)),
        'disabled_at'   => null,
        'discounts'     => [
            ['min_quantity' => 1, 'percent' => 2],
            ['min_quantity' => 2, 'percent' => 3],
            ['min_quantity' => 3, 'percent' => 5],
            ['min_quantity' => 4, 'percent' => 7],
        ],
        'duration'      => $duration,
        'free_shipping' => $faker->boolean(75),
        'frequency'     => $durations[$duration],
        'title'         => $faker->catchPhrase(),
    ];
});
