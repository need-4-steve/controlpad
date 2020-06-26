<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReservedItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reserved_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('item_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->integer('cartline_id')->unsigned()->nullable();
            $table->integer('bundle_cart_id')->unsigned()->nullable();
            $table->integer('quantity')->unsigned();
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('cartline_id')->references('id')->on('cartlines')->onDelete('cascade');
            $table->foreign('bundle_cart_id')->references('id')->on('bundle_cart')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reserved_items');
    }
}
