<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrdersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('transaction_id');
            $table->integer('customer_id');
            $table->integer('store_owner_user_id');
            $table->string('receipt_id')->nullable();
            $table->integer('type_id'); //'rep_to_customer', 'corp_to_customer', 'corp_to_rep', 'rep_to_rep'
            $table->double('total_price', 8, 2);
            $table->double('subtotal_price', 8, 2);
            $table->double('total_tax', 8, 2);
            $table->double('total_shipping', 8, 2);
            $table->integer('shipping_rate_id');
            $table->double('total_discount', 8, 2);
            $table->integer('party_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->boolean('cash');
            $table->string('mobile')->nullable();
            $table->string('status')->default('unfulfilled');
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
        Schema::dropIfExists('orders');
    }
}
