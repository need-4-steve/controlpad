<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

class AddSettingWholesaleCartMin extends Migration
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
                'key' => 'wholesale_cart_min',
                'value' => '{"value": "dollar", "show": false}',
                'category' => 'store',
            ]);
            Setting::create([
                'user_id' => 1,
                'key' => 'wholesale_cart_min_amount',
                'value' => '{"value": "1", "show": false}',
                'category' => 'store',
            ]);
            Setting::where('key', 'title_store')->update(['category' => 'store']);
            Setting::where('key', 'store_builder_admin')->update(['category' => 'store']);
            Setting::where('key', 'store_builder_reseller')->update(['category' => 'store']);
            Setting::where('key', 'use_built_in_store')->update(['category' => 'store']);
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
