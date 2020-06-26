<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_levels', function (Blueprint $table) {
            $table->increments('id');
            $table->string('carrier_id');
            $table->string('token');
            $table->string('name');
            $table->timestamps();
        });

        $seeder = new \ServiceLevelsTableSeeder;
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_levels');
    }
}
