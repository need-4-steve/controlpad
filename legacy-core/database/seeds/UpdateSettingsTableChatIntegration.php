<?php

use Illuminate\Database\Seeder;
use App\Models\Setting;

class UpdateSettingsTableChatIntegration extends Seeder
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
            'key' => 'olark_chat_integration',
            'value' => '{"value": " ", "show": false}',
            'category' => 'general',
        ]);

        Setting::create([
            'user_id' => 1,
            'key' => 'tawk_chat_integration',
            'value' => '{"value": " ", "show": false}',
            'category' => 'general',
        ]);
    }
}
