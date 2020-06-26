<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCartlinesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cartlines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cart_id');
            $table->integer('item_id');
            $table->integer('quantity');
            $table->double('price', 8, 2);
            $table->boolean('corporate')->nullable();
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
        Schema::dropIfExists('cartlines');
    }
}
