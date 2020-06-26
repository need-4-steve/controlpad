<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\OauthDriver;

class AddToSocialiteDriverTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $driver = OauthDriver::all();
        if ($driver and count($driver) > 1) {
            $seeder = new \UpdateSocialiteDriversTableSeeder;
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
