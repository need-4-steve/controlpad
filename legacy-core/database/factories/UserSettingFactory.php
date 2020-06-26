<?php

use App\Models\UserSetting;

$factory->define(UserSetting::class, function (Faker\Generator $faker) {
    return [
        'show_new_inventory' => true,
        'show_address' => $faker->boolean(90),
        'show_phone' => $faker->boolean(90),
        'show_email' => $faker->boolean(90),
        'show_location' => $faker->boolean(90),
        'will_deliver' => $faker->boolean(90),
        'new_customer_message' => $faker->text(100),
        'order_confirmation_message' => $faker->text(100)
    ];
});
