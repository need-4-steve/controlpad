<?php

use App\User;
use Carbon\Carbon;

$factory->define(App\User::class, function ($faker) {
    return [
        'role_id' => 3,
        'first_name' => $faker->unique()->firstName,
        'last_name' => $faker->unique()->lastName,
        'email' => $faker->unique()->safeEmail,
        'phone_number' => substr($faker->e164PhoneNumber, -10),
    ];
});
