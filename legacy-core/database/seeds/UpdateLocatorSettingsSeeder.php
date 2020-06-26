<?php

use Illuminate\Database\Seeder;
use App\Models\Setting;

class UpdateLocatorSettingsSeeder extends Seeder
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
            'key' => 'rep_locator_max_results',
            'value' => '{"value": 10, "show": false}',
            'category' => 'rep'
        ]);
        DB::commit();
    }
}
