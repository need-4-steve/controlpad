<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAutoshipSubscriptionLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('autoship_subscription_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->string('pid')->index();
            $table->string('tax_class')->default('');
            $table->integer('autoship_subscription_id')->index()->references('id')->on('autoship_subscriptions');
            $table->timestamps();
            $table->softDeletes();
            $table->dateTime('disabled_at')->nullable();
            // Cartline related fields
            $table->string('inventory_owner_pid');
            $table->json('items');
            $table->integer('item_id');
            $table->double('price', 8, 2);
            $table->integer('quantity');
            $table->integer('bundle_id')->nullable();
            $table->string('bundle_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('autoship_subscription_lines');
    }
}
