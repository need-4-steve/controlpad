<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('order_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('pid', 25);
            $table->string('receipt_id'); // Old - moving to pid
            $table->unsignedInteger('type_id');
            $table->string('confirmation_code', 10);
            $table->unsignedInteger('customer_id');
            $table->string('buyer_pid', 25);
            $table->string('buyer_first_name');
            $table->string('buyer_last_name');
            $table->string('buyer_email');
            $table->unsignedInteger('store_owner_user_id');
            $table->string('seller_pid', 25);
            $table->string('seller_name');
            $table->decimal('total_price', 8, 2);
            $table->decimal('subtotal_price', 8, 2);
            $table->decimal('total_discount', 8, 2);
            $table->decimal('total_tax', 8, 2);
            $table->decimal('total_shipping', 8, 2);
            $table->string('tax_invoice_pid', 25)->nullable();
            $table->boolean('taxes_committed')->default(0);
            $table->unsignedInteger('shipping_rate_id')->nullable();
            $table->unsignedInteger('coupon_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->boolean('cash');
            $table->string('source');
            $table->string('status');
            $table->integer('comm_engine_status_id')->default(1);
            $table->boolean('tax_not_charged')->default(0);
            $table->string('transaction_id', 25);
            $table->string('gateway_reference_id', 64);
            $table->timestamps();

            $table->softDeletes();

            $table->foreign('type_id')->references('id')->on('order_type');
        });

        Schema::create('orderlines', function (Blueprint $table) {
            $table->increments('id');
            $table->string('pid', 25);
            $table->unsignedInteger('order_id');
            $table->unsignedInteger('item_id')->nullable();
            $table->unsignedInteger('bundle_id')->nullable();
            $table->string('name');
            $table->string('bundle_name')->nullable();
            $table->text('items')->nullable(); // If a bundle, json serialized
            $table->string('type'); // Old we can infer this from item_id/bundle_id
            $table->unsignedInteger('quantity');
            $table->double('price', 8, 2);
            $table->unsignedInteger('inventory_owner_id');
            $table->string('inventory_owner_pid', 25);
            $table->double('discount_amount', 8, 2)->default(0.00);
            $table->unsignedInteger('discount_type_id')->nullable();
            $table->double('premium_shipping_amount', 8, 2)->default(0.00);
            $table->string('custom_sku')->nullable();
            $table->string('manufacturer_sku')->nullable();
            $table->boolean('in_comm_engine')->default(0);
            $table->string('variant');
            $table->string('option');
            $table->unsignedInteger('event_id');
            $table->timestamps();

            $table->softDeletes();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });

        DB::table('order_type')->insert([
            ['id' => 1, 'name' => 'Corporate to Rep'],
            ['id' => 2, 'name' => 'Corporate to Customer'],
            ['id' => 3, 'name' => 'Rep to Customer'],
            ['id' => 4, 'name' => 'Rep to Rep'],
            ['id' => 5, 'name' => 'Corporate to Admin'],
            ['id' => 6, 'name' => 'Fulfilled by Corporate'],
            ['id' => 7, 'name' => 'Mixed'],
            ['id' => 8, 'name' => 'Transfer Inventory'],
            ['id' => 9, 'name' => 'Affiliate'],
            ['id' => 10, 'name' => 'Personal Use']
        ]);
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
