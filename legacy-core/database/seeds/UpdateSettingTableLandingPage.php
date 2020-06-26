<?php

use App\Models\Setting;
use Illuminate\Database\Seeder;

class UpdateSettingTableLandingPage extends Seeder
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
            'key' => 'landing_page',
            'value' => '{"value": "login", "show": true}',
            'category' => 'brand',
        ]);
        DB::commit();
    }
}
