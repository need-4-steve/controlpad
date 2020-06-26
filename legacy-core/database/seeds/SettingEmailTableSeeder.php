<?php

use Illuminate\Database\Seeder;
use App\Models\SettingEmail;

class SettingEmailTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        SettingEmail::create([
            'user_id' => 1,
            'key' => 'From_Address',
            'value' => 'no_reply@ControlPad.com',
        ]);
        SettingEmail::create([
            'user_id' => 1,
            'key' => 'To_Address',
            'value' => 'admin@ControlPad.com',
        ]);
        SettingEmail::create([
            'user_id' => 1,
            'key' => 'Reply_Address',
            'value' => 'admin@ControlPad.com',
        ]);
        SettingEmail::create([
            'user_id' => 1,
            'key' => 'welcome',
            'value' => 'Welcome to controlpad.  It is great to have you on the team.',
        ]);
        DB::commit();
    }
}
