<?php

use Illuminate\Database\Seeder;
use App\Models\StoreSettingsKey;

class AddFulfillmentTimeToStoreTableKeySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        StoreSettingsKey::create(['key' => 'shipping_fulfillment_time']);
    }
}
