<?php

use Illuminate\Database\Seeder;
use App\Models\Address;
use App\Models\GeoLocation;
use App\Models\User;
use App\Models\Order;

class GeoLocationSeeder extends DatabaseSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        // Create some real locations
        $address = Address::where('addressable_id', 106)
                        ->where('addressable_type', 'App\Models\User')
                        ->where('label', 'Business')
                        ->first();

        $address->update([
            'address_1' => '452 State St',
            'address_2' => '',
            'city' => 'Orem',
            'state' => 'UT',
            'zip' => '84058'
        ]);

        $geolocation = GeoLocation::create([
            'address_id' => $address->id,
            'latitude' => 40.305374,
            'longitude' => -111.698102
        ]);

        $users = User::with('addresses')->where('id', '>=', 1000)->where('role_id', 5)->get();
        foreach ($users as $user) {
            $address = $user->addresses->where('label', 'Business')->first();
            $address->update([
                'address_1' => 'Randomized Street Addresss',
                'address_2' => '',
                'city' => 'Orem',
                'state' => 'UT',
                'zip' => '84058'
            ]);

            $geolocation = GeoLocation::create([
                'address_id' => $address->id,
                'latitude' => 40.273257 + (rand(-10000, 10000) * .0001),
                'longitude' => -111.686247 + (rand(-10000, 10000) * .0001)
            ]);
        }

        $orders = Order::with('shippingAddress')->get();
        foreach ($orders as $order) {
            $address = $order->shippingAddress;
            $geolocation = GeoLocation::create([
                'address_id' => $address->id,
                'latitude' => 40.273257 + (rand(-100000, 100000) * .0001),
                'longitude' => -111.686247 + (rand(-100000, 100000) * .0001)
            ]);
        }
        DB::commit();
    }
}
