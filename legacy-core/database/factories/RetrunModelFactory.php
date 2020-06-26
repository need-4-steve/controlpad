<?php

use App\Models\Order;
use App\Models\Returnline;
use App\Models\ReturnModel;
use App\Models\ReturnStatus;

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

$factory->define(ReturnModel::class, function (Faker\Generator $faker) {
    $userId = 109;
    $orders = Order::with('lines')->where('store_owner_user_id', $userId)->get();
    $order = $orders->random();

    return [
        'user_id'           => $order->customer()->first()->id,
        'initiator_user_id' => $userId,
        'order_id'          => $order->id,
        'return_status_id'  => ReturnStatus::all()->random(),
    ];
});

$factory->define(Returnline::class, function (Faker\Generator $faker) {
});
