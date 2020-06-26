<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInventoryHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_history', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('inventory_id')->index();
            $table->integer('inventory_user_id')->index();
            $table->integer('item_id');
            $table->integer('before_quantity_available');
            $table->integer('after_quantity_available');
            $table->integer('before_quantity_staged');
            $table->integer('after_quantity_staged');
            $table->integer('auth_user_id')->nullable();
            $table->string('request_email')->nullable();
            $table->string('request_id')->nullable();
            $table->string('request_path')->nullable();
            $table->string('application');
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
        Schema::dropIfExists('inventory_history');
    }
}
