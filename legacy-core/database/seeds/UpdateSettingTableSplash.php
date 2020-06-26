<?php

use App\Models\Setting;
use Illuminate\Database\Seeder;

class UpdateSettingTableSplash extends Seeder
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
            'key' => 'merchant_category_code',
            'value' => '{"value": "5699", "show": false}',
            'category' => 'registration',
        ]);

        Setting::create([
            'user_id' => 1,
            'key' => 'reseller_payment_option',
            'value' => '{"value": "", "show": false}',
            'category' => 'rep',
        ]);
        DB::commit();
    }
}
