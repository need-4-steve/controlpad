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

$factory->define(App\Models\Product::class, function (Faker\Generator $faker) {
    $name = $faker->unique()->firstName().'-'.uniqid();
    return [
        'long_description' => $faker->paragraph(2),
        'max' => $faker->numberBetween(6, 10),
        'min' => $faker->numberBetween(0, 5),
        'name' => $name,
        'short_description' => $faker->sentence(),
        'slug' => strtolower($name),
        'type_id' => 1,
        'user_id' => $faker->randomDigitNotNull(),
        'tax_class' => substr(uniqid('', true), -8),
        'variant_label' => 'Print'
    ];
});
