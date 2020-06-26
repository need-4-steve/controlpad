<?php

use Illuminate\Database\Seeder;
use App\Models\Setting;

class UpdateSettingsYoutube extends Seeder
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
            'key' => 'reseller_youtube',
            'value' => '{"value": "Resellers have access to YouTube", "show": false}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'affiliate_youtube',
            'value' => '{"value": "Affiliate have access to YouTube", "show": false}',
            'category' => 'rep',
        ]);
    }
}
