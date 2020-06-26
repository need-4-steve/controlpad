<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

class AddLowInventoryAlertSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $settings = Setting::first();
        if (!is_null($settings)) {
            Setting::create([
                'user_id' => 1,
                'key' => 'low_inventory_alert_corp',
                'value' => '{"show": false, "value": 10}',
                'category' => 'inventory'
            ]);
            Setting::create([
                'user_id' => 1,
                'key' => 'low_inventory_alert_rep',
                'value' => '{"show": false, "value": 5}',
                'category' => 'inventory'
            ]);
            cache()->forget('globalSettings');
            cache()->forget('global-settings');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
