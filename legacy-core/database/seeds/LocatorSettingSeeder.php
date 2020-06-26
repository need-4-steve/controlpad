<?php

use App\Models\Setting;
use Illuminate\Database\Seeder;

class LocatorSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        Setting::create([
            'user_id' => 1,
            'key' => 'rep_locator_enable',
            'value' => '{"value": false, "show": true}',
            'category' => 'rep'
        ]);

        Setting::create([
            'user_id' => 1,
            'key' => 'rep_locator_radius',
            'value' => '{"value": 100, "show": false}',
            'category' => 'rep'
        ]);

        Setting::create([
            'user_id' => 1,
            'key' => 'rep_locator_map_view',
            'value' => '{"value": false, "show": false}',
            'category' => 'rep'
        ]);

        Setting::create([

            'user_id' => 1,
            'key' => 'rep_locator_random_users',
            'value' => '{"value": 10, "show": false}',
            'category' => 'rep'
        ]);
        DB::commit();
    }
}
