<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

class EwalletPendingSetting extends Migration
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
                'key' => 'affiliate_ewallet_pending_balance',
                'value' => '{"show":true, "value":""}',
                'category' => 'rep'
            ]);
            Setting::create([
                'user_id' => 1,
                'key' => 'reseller_ewallet_pending_balance',
                'value' => '{"show":true, "value":""}',
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
        Setting::where('key', '=', 'affiliate_ewallet_pending_balance')->delete();
        Setting::where('key', '=', 'affiliate_ewallet_pending_balance')->delete();
    }
}
