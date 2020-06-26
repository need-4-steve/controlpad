<?php

use App\Models\Setting;
use Illuminate\Database\Seeder;

class UpdateSettingTableSubGracePeriod extends Seeder
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
            'key' => 'sub_grace_period',
            'value' => '{"value": "45", "show": false}',
            'category' => 'rep',
        ]);
        DB::commit();
    }
}
