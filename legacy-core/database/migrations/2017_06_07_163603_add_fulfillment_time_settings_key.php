<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\StoreSettingsKey;

class AddFulfillmentTimeSettingsKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $storeSettingsKey = StoreSettingsKey::all();
        if ((count($storeSettingsKey) > 0)) {
            $seederKey = new \AddFulfillmentTimeToStoreTableKeySeeder;
            $seederKey->run();
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
