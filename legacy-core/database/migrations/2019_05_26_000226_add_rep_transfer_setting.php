<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;
use App\Models\OrderType;

class AddRepTransferSetting extends Migration
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
                'key' => 'rep_transfer',
                'value' => '{"show": false, "value":"Reps can sell to other reps at wholesale price"}',
                'category' => 'rep'
            ]);
            cache()->forget('globalSettings');
            cache()->forget('global-settings');
        }
        $orderTypes = OrderType::all();
        if (!is_null($orderTypes)) {
            OrderType::create(['id' => 11, 'name' => 'Rep Transfer']);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Setting::where('key', 'rep_transfer')->delete();
        OrderType::where('id', 11)->delete();
    }
}
