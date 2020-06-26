<?php

use App\Models\Setting;
use Illuminate\Database\Seeder;

class AddNewRegistrationSettings extends Seeder
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
            'key' => 'collect_phone_on_registration',
            'value' => '{"value": "0", "show": false}',
            'category' => 'registration',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'require_phone_on_registration',
            'value' => '{"value": "0", "show": false}',
            'category' => 'registration',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'require_sponsor_id_on_registration',
            'value' => '{"value": "0", "show": false}',
            'category' => 'registration',
        ]);
        DB::commit();
    }
}
