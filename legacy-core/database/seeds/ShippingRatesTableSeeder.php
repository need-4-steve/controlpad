<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ShippingRate;

class ShippingRatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        $users = User::doesntHave('shipRates')->get();
        $corporateShippingRates = ShippingRate::where('user_id', 1)->where('type', 'wholesale')->first();
        foreach ($users as $user) {
            ShippingRate::create([
                'user_id' => $user->id,
                'amount' => 5,
                'min' => 0,
                'max' => 20,
                'type' => 'retail',
                'name' => 'Small Shopper Rate'
            ]);
            ShippingRate::create([
                'user_id' => $user->id,
                'amount' => 9,
                'min' => 20.01,
                'max' => 30.00,
                'type' => 'retail',
                'name' => 'Medium Shopper Rate'
            ]);
            ShippingRate::create([
                'user_id' => $user->id,
                'amount' => 15,
                'min' => 30.01,
                'max' => 99.99,
                'type' => 'retail',
                'name' => 'Big Shopper Rate'
            ]);
            ShippingRate::create([
                'user_id' => $user->id,
                'amount' => 0,
                'min' => 100,
                'max' => null,
                'type' => 'retail',
                'name' => 'Super Shopper Rate'
            ]);
        }
        if (empty($corporateShippingRates)) {
            ShippingRate::create([
                'user_id' => 1,
                'amount' => 5,
                'min' => 0,
                'max' => 20,
                'type' => 'wholesale',
                'name' => 'Small Shopper Rate'
            ]);
            ShippingRate::create([
                'user_id' => 1,
                'amount' => 9,
                'min' => 20.01,
                'max' => 30.00,
                'type' => 'wholesale',
                'name' => 'Medium Shopper Rate'
            ]);
            ShippingRate::create([
                'user_id' =>1,
                'amount' => 15,
                'min' => 30.01,
                'max' => 99.99,
                'type' => 'wholesale',
                'name' => 'Big Shopper Rate'
            ]);
            ShippingRate::create([
                'user_id' => 1,
                'amount' => 0,
                'min' => 100,
                'max' => null,
                'type' => 'wholesale',
                'name' => 'Super Shopper Rate'
            ]);
        }
        DB::commit();
    }
}
