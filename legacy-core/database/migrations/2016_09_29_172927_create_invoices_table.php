<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('customer_id')->nullable();
            $table->integer('store_owner_user_id');
            $table->string('token')->unique();
            $table->dateTime('expires_at');
            $table->double('subtotal_price', 8, 2);
            $table->double('total_shipping', 8, 2);
            $table->integer('type_id'); //'rep_to_customer', 'corp_to_customer', 'corp_to_rep', 'rep_to_rep'
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
        Schema::dropIfExists('invoices');
    }
}
