<?php

use App\Models\Setting;
use Illuminate\Database\Seeder;

class UpdateSettingTablePayquicker extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        if (empty(Setting::where('key', 'payquicker')->first())) {
            Setting::create([
                'user_id' => 1,
                'key' => 'payquicker',
                'value' => '{"value": " ", "show": false}',
                'category' => 'general',
            ]);
        }
        DB::commit();
    }
}
