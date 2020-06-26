<?php

use App\Models\Category;

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

$factory->define(Category::class, function (Faker\Generator $faker) {
    $lastCategory = Category::where('parent_id', null)
        ->where('level', 0)
        ->orderBy('placement', 'DESC')
        ->first();

    $placement = 0;
    if ($lastCategory) {
        $placement = $lastCategory->placement + 1;
    }

    return [
        'name' => $faker->colorName,
        'placement' => $placement
    ];
});
