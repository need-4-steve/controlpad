<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBundleCartTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bundle_cart', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('bundle_id')->unsigned()->index();
            $table->integer('cart_id')->unsigned()->index();
            $table->integer('quantity')->unsigned()->index();
            $table->integer('corporate')->unsigned()->index();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bundle_cart');
    }
}
