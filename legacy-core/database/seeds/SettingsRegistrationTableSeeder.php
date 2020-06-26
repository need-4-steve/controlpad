<?php

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsRegistrationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*
         * Note : these can be added without running seeders by going to the 'Settings' page and
         * saving the registration settings
         */
        DB::beginTransaction();
        Setting::create([
            'user_id' => 1,
            'key' => 'register_with_controlpad_api',
            'value' => '{"value": "allow registration through controlpad api", "show": true}',
            'category' => 'registration',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'register_without_controlpad_api',
            'value' => '{"value": "allow registration through built in registration", "show": true}',
            'category' => 'registration',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'rep_edit_information',
            'value' => '{"value": "allow reps to edit their own information", "show": true}',
            'category' => 'rep',
        ]);
        DB::commit();
    }
}
