<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCheckoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checkouts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('pid', 25);
            $table->string('cart_pid', 25)->nullable();
            $table->string('buyer_pid', 25)->nullable();
            $table->string('seller_pid', 25);
            $table->string('inventory_user_pid', 25);
            $table->string('type', 64);
            $table->decimal('total', 8, 2)->default(0.00);
            $table->decimal('subtotal', 8, 2)->default(0.00);
            $table->decimal('discount', 8, 2)->default(0.00);
            $table->decimal('tax', 8, 2)->default(0.00);
            $table->decimal('shipping', 8, 2)->default(0.00);
            $table->unsignedInteger('coupon_id')->nullable();
            $table->string('tax_invoice_pid', 25)->nullable();
            $table->unsignedInteger('shipping_rate_id')->nullable();
            $table->text('billing_address')->nullable();
            $table->text('shipping_address')->nullable();
            $table->boolean('shipping_is_billing')->default(0);
            $table->string('transfer_pid', 25)->nullable();
            $table->text('lines');
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
        Schema::dropIfExists('checkouts');
    }
}
