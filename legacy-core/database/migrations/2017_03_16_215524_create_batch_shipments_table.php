<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBatchShipmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('batch_shipments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('batch_label_id');
            $table->integer('order_id');
            $table->string('shippo_id')->nullable();
            $table->integer('parcel_template_id')->nullable();
            $table->decimal('weight')->nullable();
            $table->string('mass_unit')->nullable();
            $table->integer('carrier_id')->nullable();
            $table->integer('service_level_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('batch_shipments');
    }
}
