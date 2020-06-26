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

$factory->define(App\Models\Variant::class, function (Faker\Generator $faker) {
    return [
        'max' => $faker->randomDigit(),
        'min' => $faker->randomDigit(),
        'name' => $faker->unique()->firstName().'-'.uniqid(),
        'option_label' => 'Size',
        'product_id' => 0,
        'description' => $faker->text(255),
    ];
});

$factory->defineAs(App\Models\Variant::class, 'withRelations', function (Faker\Generator $faker) {
    return [
        'max' => $faker->randomDigit(),
        'min' => $faker->randomDigit(),
        'name' => $faker->unique()->firstName().'-'.uniqid(),
        'option_label' => 'Size',
        'description' => $faker->text(255),
        'product_id' => function () {
            return factory(App\Models\Product::class)->create()->id;
        },
    ];
});
