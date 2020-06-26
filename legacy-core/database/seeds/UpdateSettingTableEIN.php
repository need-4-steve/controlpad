<?php

use App\Models\Setting;
use Illuminate\Database\Seeder;

class UpdateSettingTableEIN extends Seeder
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
            'key' => 'ein',
            'value' => '{"value": " ", "show": false}',
            'category' => 'general',
        ]);
        DB::commit();
    }
}
