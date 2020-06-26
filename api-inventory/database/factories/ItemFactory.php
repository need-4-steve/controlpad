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

$factory->define(App\Models\Item::class, function (Faker\Generator $faker) {
    $sizes = ['Small', 'Medium', 'Large', 'X-Large', 'XX-Large', 'XXX-Large'];
    return [
        'product_id' => 0,
        'variant_id' => 0,
        'size' => $sizes[array_rand($sizes)],
        'print' => $faker->colorName(),
        'location' => substr(str_shuffle("ABCDEFGHJKLMNOPQRSTUVWXYZ"), -7),
        'manufacturer_sku' => $faker->uuid(),
    ];
});

$factory->defineAs(App\Models\Item::class, 'withRelations', function (Faker\Generator $faker) {
    $variant = factory(App\Models\Variant::class, 'withRelations')->create();
    $sizes = ['Small', 'Medium', 'Large', 'X-Large', 'XX-Large', 'XXX-Large'];
    return [
        'product_id' => $variant->product_id,
        'variant_id' => $variant->id,
        'size' => $sizes[array_rand($sizes)],
        'print' => $faker->colorName(),
        'location' => substr(str_shuffle("ABCDEFGHJKLMNOPQRSTUVWXYZ"), -7),
        'manufacturer_sku' => $faker->uuid(),
    ];
});
