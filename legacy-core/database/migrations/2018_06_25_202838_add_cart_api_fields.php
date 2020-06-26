<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCartApiFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->string('pid', 25)->nullable();
            $table->string('buyer_pid', 25)->nullable();
            $table->string('seller_pid', 25)->nullable();
            $table->string('inventory_user_pid', 25)->nullable();
            $table->unsignedInteger('coupon_id')->nullable();
            $table->string('type', 64)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn(['pid', 'buyer_pid', 'seller_pid', 'inventory_user_pid', 'coupon_id', 'type']);
        });
    }
}
