<?php

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Carbon\Carbon;
use App\Models\Item;

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

$factory->define(Invoice::class, function ($faker) {
    return [
        'token' => uniqid(),
        'expires_at' => Carbon::tomorrow(),
        'subtotal_price' => floatval(rand(5, 10).".".rand(0, 99)),
        'total_shipping' => floatval(rand(2, 5).".".rand(0, 99)),
        'total_discount' => rand(1, 3)
    ];
});

$factory->define(InvoiceItem::class, function ($faker) {
    $item = Item::with('msrp')->whereNotIn('product_id', [24, 25, 26])->inRandomOrder()->first();
    $quantity = rand(3, 5);
    return [
        'quantity' => rand(3, 5),
        'item_id' => $item->id,
        'price' => $item->msrp->price,
    ];
});
