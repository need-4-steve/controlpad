<?php

use App\Models\Coupon;
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

$factory->define(Coupon::class, function (Faker\Generator $faker) {
    $user = User::where('role_id', 5)
                ->orWhere('id', config('site.apex_user_id'))
                ->get()
                ->random(1);

    return [
        'code'        => uniqid(),
        'owner_id'    => $user->id,
        'amount'      => round(random_int(100, 1500) * .01, 2),
        'is_percent'  => $faker->boolean(),
        'title'       => $faker->words(4, true),
        'description' => $faker->sentences(1, true),
        'max_uses'    => rand(50, 100),
        'uses'        => rand(1, 30),
        'expires_at'  => null,
        'type' => 'wholesale'
    ];
});
