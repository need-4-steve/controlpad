<?php

use App\Models\Setting;
use Illuminate\Database\Seeder;

class OrderReceivedEmailSetting extends Seeder
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
            'key' => 'order_notification_email',
            'value' => '{"value": "admin@controlpad.com", "show": true}',
            'category' => 'brand',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'from_email',
            'value' => '{"value": "no-reply@controlpad.com", "show": true}',
            'category' => 'brand',
        ]);
        DB::commit();
    }
}
