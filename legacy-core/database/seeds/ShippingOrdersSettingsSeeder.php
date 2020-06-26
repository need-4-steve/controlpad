<?php

use App\Models\Setting;
use Illuminate\Database\Seeder;

class ShippingOrdersSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::create([
            'user_id' => 1,
            'key' => 'auto_transfer_orders',
            'value' => json_encode(['value' => false, 'show' => false]),
            'category' => 'shipping',
        ]);
    }
}
