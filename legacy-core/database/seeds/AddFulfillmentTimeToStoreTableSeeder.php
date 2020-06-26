<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\StoreSettingsKey;
use App\Models\StoreSetting;

class AddFulfillmentTimeToStoreTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        $users = User::where('role_id', '>', 4)->get();
        $storeSettingKey = StoreSettingsKey::where('key', 'shipping_fulfillment_time')->first();
        foreach ($users as $user) {
                $storeSetting = new StoreSetting();
                $storeSetting->user_id = $user->id;
                $storeSetting->key_id = $storeSettingKey->id;
                $storeSetting->value = "Orders ship within 2 business days";
                $storeSetting->save();
        }
        DB::commit();
    }
}
