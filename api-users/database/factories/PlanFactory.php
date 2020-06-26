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

$factory->define(App\Plan::class, function (Faker\Generator $faker) {
    return [
        'description' => implode($faker->sentences(1)),
        'duration' => 1,
        'free_trial_time' => rand(0, 30),
        'on_sign_up' => rand(0, 1),
        'renewable' => rand(0, 1),
        'seller_type_id' => rand(1, 2),
        'tax_class' => '00000000',
        'title' => $faker->catchPhrase(),
        'plan_price' => rand(5, 20),
    ];
});
