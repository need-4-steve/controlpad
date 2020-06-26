<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('pid', 25);
            $table->string('buyer_pid', 25);
            $table->string('seller_pid', 25);
            $table->string('inventory_user_pid', 25);
            $table->unsignedInteger('coupon_id')->nullable();
            $table->string('type', 64);
            $table->timestamps();
        });
        Schema::create('cartlines', function (Blueprint $table) {
            $table->increments('id');
            $table->string('pid', 25);
            $table->unsignedInteger('cart_id');
            $table->unsignedInteger('item_id')->nullable();
            $table->unsignedInteger('bundle_id')->nullable();
            $table->string('bundle_name')->nullable();
            $table->string('tax_class')->nullable();
            $table->text('items')->nullable(); // If a bundle, json serialized
            $table->unsignedInteger('quantity');
            $table->double('price', 8, 2);
            $table->string('inventory_owner_pid', 25);
            $table->double('discount', 8, 2)->default(0.00);
            $table->unsignedInteger('discount_type_id')->nullable();
            $table->unsignedInteger('event_id')->nullable();
            $table->timestamps();

            $table->foreign('cart_id')->references('id')->on('carts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('carts');
        Schema::dropIfExists('cartlines');
    }
}
