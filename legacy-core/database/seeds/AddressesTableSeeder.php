<?php

use App\Models\Address;
use App\Models\User;

class AddressesTableSeeder extends DatabaseSeeder
{
    public function run()
    {
        DB::beginTransaction();
        $faker = Faker\Factory::create();
        $users = User::doesntHave('addresses')->where('id', '!=', config('site.apex_user_id'))->get();
        $addresses = Address::where('addressable_type', User::class)->where('addressable_id', config('site.apex_user_id'))->first();
        //apex user address
        if (empty($addresses)) {
            $apexAddress = [
                'name' => config('site.company_name'),
                'address_1' => '1411 W 1250 S',
                'address_2' => '',
                'city' => 'Orem',
                'state' => 'UT',
                'addressable_id' => config('site.apex_user_id'),
                'addressable_type' => User::class,
                'zip' => 84058,
                'label' => 'Billing',
            ];
            DB::table('addresses')->insert($apexAddress);
            $apexAddress['label'] = 'Shipping';
            DB::table('addresses')->insert($apexAddress);
            $apexAddress['label'] = 'Business';
            DB::table('addresses')->insert($apexAddress);
        }
        foreach ($users as $user) {
            $address = factory(Address::class, 1)->make([
                    'name' => $user->first_name.' '.$user->last_name,
                    'addressable_id' => $user->id,
                    'label' => 'Billing',
            ])->toArray();

            DB::table('addresses')->insert($address);
            $address['label'] = 'Shipping';
            DB::table('addresses')->insert($address);
            $address['label'] = 'Business';
            DB::table('addresses')->insert($address);
        }
        DB::commit();
    }
}
