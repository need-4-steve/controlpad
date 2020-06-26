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

$factory->defineAs(App\Models\Price::class, 'premium', function (Faker\Generator $faker) {
    return [
        'price_type_id' => 3,
        'price' => money_format("%!n", $faker->numberBetween(100, 3500) / 100),
        'priceable_type' => App\Models\Item::class,
        'priceable_id' => 0,
    ];
});

$factory->defineAs(App\Models\Price::class, 'retail', function (Faker\Generator $faker) {
    return [
        'price_type_id' => 2,
        'price' => money_format("%!n", $faker->numberBetween(100, 3500) / 100),
        'priceable_type' => App\Models\Item::class,
        'priceable_id' => 0,
    ];
});

$factory->defineAs(App\Models\Price::class, 'wholesale', function (Faker\Generator $faker) {
    return [
        'price_type_id' => 1,
        'price' => money_format("%!n", $faker->numberBetween(100, 3500) / 100),
        'priceable_type' => App\Models\Item::class,
        'priceable_id' => 0,
    ];
});

$factory->defineAs(App\Models\Price::class, 'inventory', function (Faker\Generator $faker) {
    return [
        'price_type_id' => 4,
        'price' => money_format("%!n", $faker->numberBetween(100, 3500) / 100),
        'priceable_type' => App\Models\Inventory::class,
        'priceable_id' => 0,
    ];
});

$factory->defineAs(App\Models\Price::class, 'bundle', function (Faker\Generator $faker) {
    return [
        'price_type_id' => 1,
        'price' => money_format("%!n", $faker->numberBetween(100, 3500) / 100),
        'priceable_type' => App\Models\Bundle::class,
        'priceable_id' => 0,
    ];
});
