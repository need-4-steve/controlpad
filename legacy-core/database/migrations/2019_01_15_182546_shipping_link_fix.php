<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

class ShippingLinkFix extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $settings = Setting::all();
        $shippingLinkSetting = Setting::where('key', 'shipping_link')->first();
        if (!is_null($settings) && is_null($shippingLinkSetting)) {
            Setting::create([
                'user_id' => 1,
                'key' => 'shipping_link',
                'value' => '{"value": "", "show": false}',
                'category' => 'rep'
            ]);
            Setting::create([
                'user_id' => 1,
                'key' => 'shipping_link_text',
                'value' => '{"value": "Shipping Link", "show": false}',
                'category' => 'rep'
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
