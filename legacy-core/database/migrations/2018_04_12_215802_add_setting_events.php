<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

class AddSettingEvents extends Migration
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
                'key' => 'allow_reps_events_img',
                'value' => '{"value": "Allow images by reps", "show": false}',
                'category' => 'events',
            ]);
            Setting::create([
                'user_id' => 1,
                'key' => 'allow_reps_events',
                'value' => '{"value": "Allow reps to have events on", "show": false}',
                'category' => 'events',
            ]);
            Setting::create([
                'user_id' => 1,
                'key' => 'events_as_replicated_site',
                'value' => '{"value": "Make events page the landing page for replicated sites ", "show": false}',
                'category' => 'events',
            ]);
            Setting::create([
                'user_id' => 1,
                'key' => 'events_default_img',
                'value' => '{"value": ""}',
                'category' => 'events',
            ]);
            cache()->flush();
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
