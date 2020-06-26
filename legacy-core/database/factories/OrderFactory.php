<?php

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Orderline;
use App\Models\User;
use App\Models\Item;

$this->transaction_ids = [
    109888830,
    109888851,
    109888860,
    109888863,
    109888872,
    109888902,
    109888911,
    '1bTCgQWW6tBFgi',
    '1bmOTr6bksADbL',
    '1bmOVMmCgq8APY',
    '1bmOVguY5jQuIl',
    '1bmOW259TxNyko',
    '1bmOWgthq72rDS',
    '1bmOZ8m68PbnAS',
    '1bmOZHbx1E6ojD',
    '1bocemLWaKtTiA'
];

$this->source = [
    null,
    'ios'
];

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

$factory->define(Order::class, function () {
    $date = Carbon::now()->subDays(mt_rand(0, 30));
    return [
        'customer_id'         => 200,
        'store_owner_user_id' => config('site.apex_user_id'),
        'receipt_id'          => uniqid(),
        'type_id'             => 1,
        'total_price'         => 0,
        'subtotal_price'      => 0,
        'total_tax'           => 0,
        'total_shipping'      => 0,
        'total_discount'      => 0,
        'cash'                => false,
        'source'              => $this->source[mt_rand(0, 1)],
        'updated_at'          => $date,
        'created_at'          => $date,
        'transaction_id'      => $this->transaction_ids[mt_rand(0, count($this->transaction_ids) -1)]
    ];
});

$factory->defineAs(Orderline::class, 'wholesale', function () {
    $item = Item::all()->random(1);
    return [
        'order_id' => 0,
        'item_id' => $item->id,
        'type' => $item->product->type->name,
        'name' => $item->product->name,
        'quantity' => rand(1, 3),
        'custom_sku' => $item->custom_sku,
        'manufacturer_sku' => $item->manufacturer_sku,
        'price'            => $item->prices->where('price_type_id', 1)->first()->price //Wholesale Price
    ];
});

$factory->defineAs(Orderline::class, 'retail', function () {
    $item = Item::all()->random(1);
    return [
        'item_id'          => $item->id,
        'type'             => $item->product->type->name,
        'name'             => $item->product->name,
        'quantity'         => mt_rand(1, 3),
        'custom_sku'       => $item->custom_sku,
        'manufacturer_sku' => $item->manufacturer_sku,
        'price'            => $item->prices->where('price_type_id', 2)->first()->price //Suggested Retail Price
    ];
});
