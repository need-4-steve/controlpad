<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubscriptionReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription_receipts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('transaction_id');
            $table->integer('subscription_id');
            $table->integer('user_id');
            $table->double('subtotal_price', 8, 2);
            $table->string('title');
            $table->integer('duration');
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
        Schema::dropIfExists('subscription_receipts');
    }
}
