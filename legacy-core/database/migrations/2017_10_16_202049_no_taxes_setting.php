<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

class NoTaxesSetting extends Migration
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
                'key' => 'tax_calculation',
                'value' => '{"value": "Taxes are calculated", "show": true}',
                'category' => 'taxes'
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
