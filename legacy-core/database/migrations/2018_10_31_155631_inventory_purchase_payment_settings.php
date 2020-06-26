<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

class InventoryPurchasePaymentSettings extends Migration
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
                'key' => 'wholesale_ewallet',
                'value' => '{"value": "", "show": false}',
                'category' => 'rep',
            ]);
            Setting::create([
                'user_id' => 1,
                'key' => 'wholesale_card_token',
                'value' => '{"value": "", "show": false}',
                'category' => 'rep',
            ]);
            // Affiliate team is company at the time of making this setting
            Setting::create([
                'user_id' => 1,
                'key' => 'payman_affiliate_team',
                'value' => '{"value": "company", "show": false}',
                'category' => 'checkout'
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
