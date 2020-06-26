<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\StoreSetting;

class NewTimeSettingShippingSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $storeSettings = StoreSetting::all();
        if ((count($storeSettings) > 0)) {
            $seeder = new \AddFulfillmentTimeToStoreTableSeeder;
            $seeder->run();
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
