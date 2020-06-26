<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReturnLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('return_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('return_id')->unsigned()->nullable();
            $table->integer('item_id')->unsigned()->nullable();
            $table->integer('orderline_id')->unsigned()->nullable();
            $table->integer('return_reason_id')->unsigned()->nullable();
            $table->string('type');
            $table->string('name');
            $table->double('price', 8, 2);
            $table->integer('quantity');
            $table->string('custom_sku')->nullable();
            $table->string('manufacturer_sku')->nullable();
            $table->string('comments');
            $table->timestamps();

            $table->foreign('return_id')->references('id')->on('returns');
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('orderline_id')->references('id')->on('orderlines');
            $table->foreign('return_reason_id')->references('id')->on('return_reasons');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('return_lines');
    }
}
