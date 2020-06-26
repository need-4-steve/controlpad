<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations', function($table) {
            $table->increments('id');
            $table->string('reservation_pid', 32)->unique();
            $table->string('group_pid', 32);
            $table->integer('quantity');
            $table->integer('inventory_id')->unsigned();
            $table->foreign('inventory_id')->references('id')->on('inventories');
            $table->index('group_pid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('reservations');
    }
}
