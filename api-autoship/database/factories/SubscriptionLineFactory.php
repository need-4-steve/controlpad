<?php

use App\Models\Subscription;
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

$factory->define(App\Models\SubscriptionLine::class, function (Faker\Generator $faker, $attribute) {

    $items = [
        json_decode('{"id":1,"inventory_id":1,"product_name":"Adah Belt","variant_name":"MediumSlateBlue","option_label":"Size","option":"S","sku":"9786236722053","premium_shipping_cost":null,"img_url":"https:\/\/s3-us-west-2.amazonaws.com\/controlpad-hub\/efa9d3df24c7538c18c53faf9751cb82.jpg"}', true),
        json_decode('{"id":17,"inventory_id":17,"product_name":"Tianna Belt","variant_name":"CornflowerBlue","option_label":"Size","option":"M","sku":"9791241733178","premium_shipping_cost":null,"img_url":"https:\/\/s3-us-west-2.amazonaws.com\/controlpad-hub\/6ef60cc6fbeeccb7c107ed23ca7bdc89.jpg"}', true),
        json_decode('{"id":28,"inventory_id":28,"product_name":"Zora Belt","variant_name":"Cornsilk","option_label":"Size","option":"L","sku":"9787654001300","premium_shipping_cost":null,"img_url":"https:\/\/s3-us-west-2.amazonaws.com\/controlpad-hub\/efa9d3df24c7538c18c53faf9751cb82.jpg"}', true),
        json_decode('{"id":34,"inventory_id":34,"product_name":"Neoma Bow Tie","variant_name":"Aqua","option_label":"Size","option":"XL","sku":"9791322672747","premium_shipping_cost":null,"img_url":"https:\/\/s3-us-west-2.amazonaws.com\/controlpad-hub\/8cdfdee7a5833978f98eb67376cddeb8.jpg"}', true),
        json_decode('{"id":40,"inventory_id":40,"product_name":"Lily Bow Tie","variant_name":"Tan","option_label":"Size","option":"XXL","sku":"9783181900710","premium_shipping_cost":null,"img_url":"https:\/\/s3-us-west-2.amazonaws.com\/controlpad-hub\/3bcf58ec4f1bc02a4eb9871f5a836145.jpeg"}', true),
    ];

    $item = $faker->randomElement($items);

    $subscription = factory(Subscription::class)->create(['next_billing_at' => Carbon::now()]);
    return [
        'item_id'                   => $item['id'],
        'inventory_owner_pid'       => $subscription->seller_pid,
        'tax_class'                 => '09050101',
        'items'                     => [$item],
        'price'                     => rand(1, 10),
        'quantity'                  => rand(1, 5),
        'autoship_subscription_id'  => $subscription->id,
    ];
});

$factory->defineAs(App\Models\SubscriptionLine::class, 'Seeder', function (Faker\Generator $faker) {
    $subscription = factory(Subscription::class, 'Seeder')->create();
    $items = [
        json_decode('{"id":1,"inventory_id":1,"product_name":"Adah Belt","variant_name":"MediumSlateBlue","option_label":"Size","option":"S","sku":"9786236722053","premium_shipping_cost":null,"img_url":"https:\/\/s3-us-west-2.amazonaws.com\/controlpad-hub\/efa9d3df24c7538c18c53faf9751cb82.jpg"}', true),
        json_decode('{"id":17,"inventory_id":17,"product_name":"Tianna Belt","variant_name":"CornflowerBlue","option_label":"Size","option":"M","sku":"9791241733178","premium_shipping_cost":null,"img_url":"https:\/\/s3-us-west-2.amazonaws.com\/controlpad-hub\/6ef60cc6fbeeccb7c107ed23ca7bdc89.jpg"}', true),
        json_decode('{"id":28,"inventory_id":28,"product_name":"Zora Belt","variant_name":"Cornsilk","option_label":"Size","option":"L","sku":"9787654001300","premium_shipping_cost":null,"img_url":"https:\/\/s3-us-west-2.amazonaws.com\/controlpad-hub\/efa9d3df24c7538c18c53faf9751cb82.jpg"}', true),
        json_decode('{"id":34,"inventory_id":34,"product_name":"Neoma Bow Tie","variant_name":"Aqua","option_label":"Size","option":"XL","sku":"9791322672747","premium_shipping_cost":null,"img_url":"https:\/\/s3-us-west-2.amazonaws.com\/controlpad-hub\/8cdfdee7a5833978f98eb67376cddeb8.jpg"}', true),
        json_decode('{"id":40,"inventory_id":40,"product_name":"Lily Bow Tie","variant_name":"Tan","option_label":"Size","option":"XXL","sku":"9783181900710","premium_shipping_cost":null,"img_url":"https:\/\/s3-us-west-2.amazonaws.com\/controlpad-hub\/3bcf58ec4f1bc02a4eb9871f5a836145.jpeg"}', true),
    ];
    $item = $faker->randomElement($items);
    return [
        'item_id'                   => $item['id'],
        'inventory_owner_pid'       => $subscription->seller_pid,
        'tax_class'                 => '09050101',
        'items'                     => [$item],
        'price'                     => round(rand(100, 1000) / 100, 2),
        'quantity'                  => rand(1, 5),
        'autoship_subscription_id'  => $subscription->id,
    ];
});
