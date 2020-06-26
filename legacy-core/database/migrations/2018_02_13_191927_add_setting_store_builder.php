<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

class AddSettingStoreBuilder extends Migration
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
                'key' => 'store_builder_admin',
                'value' => '{"value": "Admin can Use Store Builder", "show": false}',
                'category' => 'general',
            ]);
            Setting::create([
                'user_id' => 1,
                'key' => 'store_builder_reseller',
                'value' => '{"value": "Reseller can Use Store Builder", "show": false}',
                'category' => 'general',
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
