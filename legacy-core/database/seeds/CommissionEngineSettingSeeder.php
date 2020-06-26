<?php

use Illuminate\Database\Seeder;
use App\Models\Setting;

class CommissionEngineSettingSeeder extends Seeder
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
            'key' => 'use_commission_engine',
            'value' => json_encode(['value' => false, 'show' => false]),
            'category' => 'commission_engine',
        ]);
    }
}
