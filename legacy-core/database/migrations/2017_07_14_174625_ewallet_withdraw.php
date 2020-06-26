<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

class EwalletWithdraw extends Migration
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
                'key' => 'affiliate_ewallet_withdraw',
                'value' => '{"value": "Affiliates have access to withdraw from their balance", "show": false}',
                'category' => 'rep',
            ]);
            Setting::create([
                'user_id' => 1,
                'key' => 'reseller_ewallet_withdraw',
                'value' => '{"value": "Resellers have access to withdraw from their balance", "show": false}',
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
