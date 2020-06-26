<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

class AddAutoshipSettings extends Migration
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
                'key' => 'autoship_wholesale',
                'value' => '{"value": false, "show": false}',
                'category' => 'auto_ship',
            ]);
            Setting::create([
                'user_id' => 1,
                'key' => 'autoship_retail',
                'value' => '{"value": false, "show": false}',
                'category' => 'auto_ship',
            ]);
            Setting::create([
                'user_id' => 1,
                'key' => 'autoship_display_name',
                'value' => '{"value": "Autoship", "show": false}',
                'category' => 'auto_ship',
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
