<?php

use App\Models\Phone;
use App\Models\User;

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

$factory->define(Phone::class, function (Faker\Generator $faker) {
    return [
        'label' => 'Personal',
        'number' => $faker->numerify('##########'),
        'type' => 'Mobile',
        'phonable_type' => User::class,
        'phonable_id' => config('site.apex_user_id')
    ];
});
