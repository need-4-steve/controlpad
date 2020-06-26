<?php

use App\Models\Bundle;
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

$factory->define(Bundle::class, function (Faker\Generator $faker) {
    $name = $faker->unique()->lastName();
    return [
        'name' => $name." Pack",
        'slug' => strtolower($name),
        'short_description' => $faker->text(20),
        'long_description' => $faker->text(),
        'user_id' => 1
    ];
});
