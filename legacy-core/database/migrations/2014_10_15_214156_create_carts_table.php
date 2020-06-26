<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCartsTable extends Migration
{

    public function up()
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uid');
            $table->integer('user_id');
            $table->double('subtotal_price', 8, 2);
            $table->double('total_tax', 8, 2);
            $table->double('total_discount', 8, 2);
            $table->double('total_price', 8, 2);
            $table->decimal('total_shipping');
            $table->integer('shipping_rate_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('carts');
    }
}
