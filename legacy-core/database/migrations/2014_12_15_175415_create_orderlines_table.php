<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrderlinesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orderlines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id');
            $table->integer('item_id')->nullable();
            $table->integer('bundle_id')->nullable();
            $table->string('bundle_name')->nullable();
            $table->string('type');
            $table->string('name');
            $table->double('price', 8, 2);
            $table->integer('quantity');
            $table->string('custom_sku')->nullable();
            $table->string('manufacturer_sku')->nullable();
            $table->boolean('corporate')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orderlines');
    }
}
