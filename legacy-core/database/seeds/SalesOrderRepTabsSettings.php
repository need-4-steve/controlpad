<?php

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SalesOrderRepTabsSettings extends Seeder
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
            'key' => 'rep_orders_tab',
            'value' => '{"value": false, "show": true}',
            'category' => 'rep',
        ]);
        Setting::create([
            'user_id' => 1,
            'key' => 'rep_sales_tab',
            'value' => '{"value": false, "show": true}',
            'category' => 'rep',
        ]);
        DB::commit();
        cache()->forget('global-settings');
    }
}
