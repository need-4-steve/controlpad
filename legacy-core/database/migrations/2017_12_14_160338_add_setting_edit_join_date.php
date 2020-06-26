<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

class AddSettingEditJoinDate extends Migration
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
                'key' => 'edit_join_date',
                'value' => '{"value": "Can edit join date.", "show": false}',
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
