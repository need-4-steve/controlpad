<?php

use App\Models\Setting;
use Illuminate\Database\Seeder;

class UpdateSettingTableSeeder extends Seeder
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
            'key' => 'sold_out',
            'value' => '{"value": "0", "show": true}',
            'category' => 'rep',
        ]);
        DB::commit();
    }
}
