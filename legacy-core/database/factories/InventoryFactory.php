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

$factory->define(App\Models\Inventory::class, function (Faker\Generator $faker) {
    return [
        'item_id' => 0,
        'user_id' => 0,
        'owner_id' => 0,
        'quantity_available' => $faker->numberBetween(100, 1000),
    ];
});

$factory->defineAs(App\Models\Inventory::class, 'withRelations', function (Faker\Generator $faker) {
    return [
        'item_id' => function () {
            return factory(App\Models\Item::class, 'withRelations')->create()->id;
        },
        'user_id' => 0,
        'owner_id' => 0,
        'quantity_available' => $faker->numberBetween(100, 1000),
    ];
});
