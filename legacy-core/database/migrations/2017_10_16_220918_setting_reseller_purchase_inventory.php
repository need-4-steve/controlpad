<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

class SettingResellerPurchaseInventory extends Migration
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
                'key' => 'reseller_purchase_inventory',
                'value' => '{"value": "Resellers can purchase inventory", "show": true}',
                'category' => 'rep',
            ]);
            Setting::create([
                'user_id' => 1,
                'key' => 'reseller_my_orders',
                'value' => '{"value": "Resellers can see orders that they have purchased", "show": true}',
                'category' => 'rep',
            ]);
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
