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

$factory->define(App\Models\TaxConnection::class, function (Faker\Generator $faker) {
    return [
        'type' => 'mock',
        'merchant_id' => strval($faker->randomNumber),
        'active' => false,
        'sandbox' => true,
        'credentials' => ['api_key' => 'somefakeapikey']
    ];
});

// Need to pass in tax_connection_id
$factory->define(App\Models\TaxInvoice::class, function (Faker\Generator $faker) {
    $subtotal = $faker->randomFloat($nbMaxDecimals = 2, $min = 5.00, $max = 300.00);
    return [
       'type' => "sale",
       "pid" => CPCommon\Pid\Pid::create(),
       "subtotal" => $subtotal,
       "tax" => round(($subtotal * 0.06), 2, PHP_ROUND_HALF_UP),
       "committed_at" => Carbon\Carbon::now()->toDateTimeString(),
       "merchant_id" => strval($faker->randomNumber())
    ];
});
