<?php

use Illuminate\Database\Seeder;
use App\Models\Setting;

class CreateJoinLinkSettingSeeder extends Seeder
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
            'key' => 'store_join_link',
            'value' => '{"value": "Join", "show": false}',
            'category' => 'rep',
        ]);
    }
}
