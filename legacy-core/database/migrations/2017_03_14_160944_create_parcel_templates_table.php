<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParcelTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parcel_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('carrier_id');
            $table->string('token');
            $table->string('name');
            $table->decimal('length');
            $table->decimal('width');
            $table->decimal('height');
            $table->string('distance_unit');
            $table->timestamps();
        });

        $seeder = new \ParcelTemplatesTableSeeder;
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parcel_templates');
    }
}
