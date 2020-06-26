<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

class EwalletPayTaxesSetting extends Migration
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
                'key' => 'affiliate_ewallet_taxes_balance',
                'value' => '{"value": "Affiliates can pay taxes with their ewallet balance", "show": false}',
                'category' => 'rep',
            ]);
            Setting::create([
                'user_id' => 1,
                'key' => 'affiliate_ewallet_taxes_ach',
                'value' => '{"value": "Affiliates can pay taxes with eCheck", "show": false}',
                'category' => 'rep',
            ]);
            Setting::create([
                'user_id' => 1,
                'key' => 'affiliate_ewallet_taxes_credit_card',
                'value' => '{"value": "Affiliates can pay taxes with credit card", "show": false}',
                'category' => 'rep',
            ]);
            Setting::create([
                'user_id' => 1,
                'key' => 'affiliate_ewallet_taxes_paid_first',
                'value' => '{"value": "Affiliates need to pay taxes first before withdrawing from eWallet", "show": true}',
                'category' => 'rep',
            ]);
            Setting::create([
                'user_id' => 1,
                'key' => 'reseller_ewallet_taxes_balance',
                'value' => '{"value": "Resellers can pay taxes with their ewallet balance", "show": false}',
                'category' => 'rep',
            ]);
            Setting::create([
                'user_id' => 1,
                'key' => 'reseller_ewallet_taxes_ach',
                'value' => '{"value": "Resellers can pay taxes with eCheck", "show": false}',
                'category' => 'rep',
            ]);
            Setting::create([
                'user_id' => 1,
                'key' => 'reseller_ewallet_taxes_credit_card',
                'value' => '{"value": "Resellers can pay taxes with credit card", "show": false}',
                'category' => 'rep',
            ]);
            Setting::create([
                'user_id' => 1,
                'key' => 'reseller_ewallet_taxes_paid_first',
                'value' => '{"value": "Resellers need to pay taxes first before withdrawing from eWallet", "show": true}',
                'category' => 'rep',
            ]);
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
