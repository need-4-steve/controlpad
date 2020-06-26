<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderCheckoutApiFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('pid', 25)->nullable();
            $table->string('buyer_pid', 25)->nullable();
            $table->string('buyer_email')->nullable();
            $table->string('seller_pid', 25)->nullable();
            $table->string('seller_name')->nullable();
            $table->string('confirmation_code', 16)->nullable();
            $table->unsignedInteger('coupon_id')->nullable();
            $table->text('billing_address')->nullable();
            $table->text('shipping_address')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('pid');
            $table->dropColumn('buyer_pid');
            $table->dropColumn('buyer_email');
            $table->dropColumn('seller_pid');
            $table->dropColumn('seller_name');
            $table->dropColumn('confirmation_code');
            $table->dropColumn('coupon_id');
            $table->dropColumn('billing_address');
            $table->dropColumn('shipping_address');
        });
    }
}
