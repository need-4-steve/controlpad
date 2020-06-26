<?php

use Illuminate\Database\Seeder;
use App\Models\Setting;

class RequireRegistrationCodeSeeder extends Seeder
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
            'key' => 'require_registration_code',
            'value' => '{"value": "7cW29G21", "show": false}',
            'category' => 'registration',
        ]);
    }
}
