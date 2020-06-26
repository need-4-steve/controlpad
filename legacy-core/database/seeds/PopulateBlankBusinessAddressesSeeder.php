<?php

use Illuminate\Database\Seeder;
use App\Models\User;

class PopulateBlankBusinessAddressesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::with('businessAddress', 'shippingAddress', 'billingAddress')
            ->whereIn('role_id', [3, 5, 7, 8])
            ->doesntHave('shippingAddress')
            ->orWhereIn('role_id', [3, 5, 7, 8])
            ->doesntHave('billingAddress')
            ->orWhereIn('role_id', [5])
            ->doesntHave('businessAddress')
            ->get();

        $addresses = [];
        foreach ($users as $user) {
            if (empty($user->shippingAddress) && !empty($user->billingAddress)) {
                $shippingAddress = $user->billingAddress->toArray();
                unset($shippingAddress['id']);
                $shippingAddress['label'] = 'Shipping';
                $addresses[] = $shippingAddress;
            }
            if (empty($user->billingAddress) && !empty($user->shippingAddress)) {
                $billingAddress = $user->shippingAddress->toArray();
                unset($billingAddress['id']);
                $billingAddress['label'] = 'Billing';
                $addresses[] = $billingAddress;
            }
            if (empty($user->businessAddress) && !empty($user->shippingAddress) && $user->role_id === 5) {
                $businessAddress = $user->shippingAddress->toArray();
                unset($businessAddress['id']);
                $businessAddress['label'] = 'Business';
                $addresses[] = $businessAddress;
            }
        }
        DB::table('addresses')->insert($addresses);
    }
}
