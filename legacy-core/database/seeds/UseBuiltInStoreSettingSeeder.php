<?php

use Illuminate\Database\Seeder;
use App\Models\Setting;

class UseBuiltInStoreSettingSeeder extends Seeder
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
            'key' => 'use_built_in_store',
            'value' => '{"show": true, "value": true}',
            'category' => 'store',
        ]);
    }
}
