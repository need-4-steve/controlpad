<?php

use Illuminate\Database\Seeder;
use App\Models\Setting;

class UpdateSettingsTableVideoCorp extends Seeder
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
            'key' => 'corp_youtube',
            'value' => '{"value": "Corporate have access to YouTube", "show": false}',
            'category' => 'general',
        ]);
    }
}
