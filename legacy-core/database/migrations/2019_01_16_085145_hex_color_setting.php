<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

class HexColorSetting extends Migration
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
                'key' => 'hex_color',
                'value' => '{"show": true,"value": "Controlpad"}',
                'category' => 'general',
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
