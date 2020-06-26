<?php

use Illuminate\Database\Seeder;

use App\Models\Price;
use App\Models\Subscription;

class SubscriptionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        $price = new Price([
            'price_type_id' => 1,
            'price'         => 24.95
        ]);

        $subscription = Subscription::create([
            'title'     => 'Example Subscription',
            'slug'      => str_slug('Example Subscription'),
            'duration'  => 1,
            'renewable' => true,
            'description' => "Description",
            'on_sign_up' => false,
            'plan_price' => $price->price,
        ]);

        $subscription->price()->save($price);
        DB::commit();
    }
}
