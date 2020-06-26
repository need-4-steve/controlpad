<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBatchLabelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('batch_labels', function (Blueprint $table) {
            $table->increments('id');
            $table->string('shippo_id')->nullable();
            $table->string('status')->nullable();
            $table->string('label_url')->nullable();
            $table->string('carrier_id')->nullable();
            $table->string('service_level_id')->nullable();
            $table->integer('parcel_template_id')->nullable();
            $table->integer('successful_labels')->nullable();
            $table->decimal('weight')->nullable();
            $table->string('mass_unit')->nullable();
            $table->double('amount', 8, 2)->nullable();
            $table->double('markup', 8, 2)->nullable();
            $table->double('total_price', 8, 2)->nullable();
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
        Schema::dropIfExists('batch_labels');
    }
}
