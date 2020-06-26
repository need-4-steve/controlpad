<?php

use App\Models\User;
use Carbon\Carbon;

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

$factory->defineAs(App\Models\User::class, 'admin', function ($faker) {
    $faker->seed(10000);
    $userName = str_replace('.', '', $faker->unique()->userName);
    return [
        'first_name' => $faker->unique()->firstName,
        'last_name'  => $faker->unique()->lastName,
        'email'      => $userName.'@'.$faker->safeEmailDomain,
        'password'   => bcrypt(config('site.testing_password')),
        'role_id'    => 7,
        'join_date' => Carbon::now()
    ];
});

$factory->defineAs(App\Models\User::class, 'customer', function ($faker) {
    $faker->seed(20000);
    return [
        'first_name' => $faker->unique()->firstName,
        'last_name' => $faker->unique()->lastName,
        'email' => uniqid().$faker->unique()->safeEmail,
        'join_date' => Carbon::now()
    ];
});

$factory->defineAs(App\Models\User::class, 'rep', function ($faker) {
    $faker->seed(1);
    $sellerTypesCount = App\Models\SellerType::count();
    $userName = str_replace('.', '', $faker->unique()->userName);
    $sponsorIds = User::where('role_id', 5)
        ->orWhere('id', config('site.apex_user_id'))
        ->pluck('id')
        ->toArray();

    return [
        'first_name' => $faker->unique()->firstName,
        'last_name'  => $faker->unique()->lastName,
        'email'      => $userName.'@'.$faker->safeEmailDomain,
        'password'   => bcrypt(config('site.testing_password')),
        'role_id'    => 5,
        'public_id'  => $userName,
        'seller_type_id' => rand(1, $sellerTypesCount),
        'sponsor_id' => $sponsorIds[array_rand($sponsorIds)],
        'join_date' => Carbon::now()
    ];
});
