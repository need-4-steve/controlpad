<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

class AddLowInventorySetting extends Migration
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
                'key' => 'wholesale_low_inventory',
                'value' => '{"value": "25", "show": false}',
                'category' => 'store',
            ]);
            Setting::create([
                'user_id' => 1,
                'key' => 'retail_low_inventory',
                'value' => '{"value": "25", "show": false}',
                'category' => 'store',
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
