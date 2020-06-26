<?php

use App\Models\Product;
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

$factory->define(Product::class, function (Faker\Generator $faker) {
    $faker->seed(1);
    $name = $faker->unique()->firstName();
    return [
        'name' => $name,
        'slug' => strtolower($name),
        'short_description' => $faker->text(20),
        'long_description' => $faker->text(),
        'type_id' => 1,
        'user_id' => 1,
        'tax_class' => '09050101',
        'variant_label' => 'Print',
    ];
});

$factory->defineAs(Product::class, 'FulfilledByCorp', function (Faker\Generator $faker) {
    $faker->seed(1000);
    $name = $faker->unique()->firstName();
    return [
        'name' => 'Premium '.$name,
        'slug' => strtolower('premium-'.$name),
        'short_description' => $faker->text(20),
        'long_description' => $faker->text(),
        'type_id' => 5,
        'user_id' => 1,
        'duration' => 7,
        'tax_class' => '09050101',
        'variant_label' => 'Print',
    ];
});

$factory->defineAs(App\Models\Product::class, 'withRelations', function (Faker\Generator $faker) {
    $name = $faker->unique()->firstName().'-'.uniqid();
    return [
        'long_description' => $faker->paragraph(2),
        'max' => null,
        'min' => null,
        'name' => $name,
        'short_description' => $faker->sentence(),
        'slug' => strtolower($name),
        'type_id' => 1,
        'user_id' => $faker->randomDigitNotNull(),
        'tax_class' => substr(uniqid('', true), -8),
        'variant_label' => 'Print'
    ];
});
