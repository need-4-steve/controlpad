<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

class SimpleCommissionSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $settings = Setting::first();
        if (!empty($settings)) {
            Setting::create([
                'user_id' => 1,
                'key' => 'simple_commissions',
                'value' => '{"value": 0, "show": false}',
                'category' => 'commission_engine',
            ]);
            Setting::create([
                'user_id' => 1,
                'key' => 'affiliate_ewallet_balance',
                'value' => '{"value": 0, "show": true}',
                'category' => 'rep',
            ]);
            Setting::create([
                'user_id' => 1,
                'key' => 'affiliate_ewallet_commission',
                'value' => '{"value": 0, "show": false}',
                'category' => 'rep',
            ]);
            Setting::create([
                'user_id' => 1,
                'key' => 'affiliate_ewallet_taxes',
                'value' => '{"value": 0, "show": true}',
                'category' => 'rep',
            ]);
            Setting::create([
                'user_id' => 1,
                'key' => 'reseller_ewallet_balance',
                'value' => '{"value": 0, "show": true}',
                'category' => 'rep',
            ]);
            Setting::create([
                'user_id' => 1,
                'key' => 'reseller_ewallet_commission',
                'value' => '{"value": 0, "show": false}',
                'category' => 'rep',
            ]);
            Setting::create([
                'user_id' => 1,
                'key' => 'reseller_ewallet_taxes',
                'value' => '{"value": 0, "show": true}',
                'category' => 'rep',
            ]);
            cache()->forget('global-settings');
            cache()->forget('globalSettings');
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
