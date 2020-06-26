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

$factory->define(App\Models\Bundle::class, function (Faker\Generator $faker) {
    $name = $faker->unique()->firstName().'-'.uniqid();
    return [
        'name' => $name,
        'slug' => strtolower($name),
        'short_description' => $faker->sentence(),
        'long_description' => $faker->paragraph(2),
        'starter_kit' => 0,
        'tax_class' => substr(uniqid('', true), -8),
        'user_id' => 1,
    ];
});
