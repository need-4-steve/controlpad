<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

class AddSettingsCustomOrderShipping extends Migration
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
                'key' => 'reseller_custom_order',
                'value' => '{"value": "Reseller can create custom orders", "show": true}',
                'category' => 'rep',
            ]);
            Setting::create([
                'user_id' => 1,
                'key' => 'reseller_custom_corp',
                'value' => '{"value": "Reseller can create custom orders from corporate inventory", "show": false}',
                'category' => 'rep',
            ]);
            Setting::create([
                'user_id' => 1,
                'key' => 'affiliate_custom_order',
                'value' => '{"value": "Affiliate can create custom orders", "show": true}',
                'category' => 'rep',
            ]);
            Setting::create([
                'user_id' => 1,
                'key' => 'affiliate_shipping_rates',
                'value' => '{"value": "Affiliate can create custom orders", "show": false}',
                'category' => 'rep',
            ]);
            cache()->flush();
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
