<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

class AutoshipPurchaseLabelSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $settings = Setting::all();
        if (isset($settings) && count($settings) > 0) {
            Setting::create([
                'user_id' => 1,
                'key' => 'autoship_purchase_label',
                'value' => '{"value": "Create Autoship", "show": false}',
                'category' => 'auto_ship',
            ]);
            Setting::create([
                'user_id' => 1,
                'key' => 'autoship_default_purchase',
                'value' => '{"value": false, "show": false}',
                'category' => 'auto_ship',
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
