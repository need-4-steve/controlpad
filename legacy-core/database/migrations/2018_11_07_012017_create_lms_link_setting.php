<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

class CreateLmsLinkSetting extends Migration
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
                'key' => 'lms_link',
                'value' => '{"value": "", "show": false}',
                'category' => 'rep'
            ]);
            Setting::create([
                'user_id' => 1,
                'key' => 'lms_link_name',
                'value' => '{"value": "LMS", "show": false}',
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
        //
    }
}
