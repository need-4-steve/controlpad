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

$factory->define(App\Models\Category::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->unique()->firstName().'-'.uniqid(),
        'parent_id' => null,
        'placement' => 0,
        'level' => 0,
        'show_on_store' => 0,
    ];
});
