<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateShippingRatesTable extends Migration
{

    public function up()
    {
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->decimal('amount');
            $table->decimal('min')->nullable();
            $table->decimal('max')->nullable();
            $table->string('type');
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipping_rates');
    }
}
