<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

class SelfPickupSettings extends Migration
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
                'key' => 'self_pickup_wholesale',
                'value' => '{"value": "allow self pickup on wholesale", "show": false}',
                'category' => 'store',
            ]);
            Setting::create([
                'user_id' => 1,
                'key' => 'self_pickup_reseller',
                'value' => '{"value": "allow self pickup for resellers", "show": false}',
                'category' => 'rep',
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
