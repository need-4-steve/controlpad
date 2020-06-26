<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReturnHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('return_history', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('return_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->integer('new_status_id')->unsigned();
            $table->integer('old_status_id')->unsigned()->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->foreign('return_id')->references('id')->on('returns');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('new_status_id')->references('id')->on('return_statuses');
            $table->foreign('old_status_id')->references('id')->on('return_statuses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('return_history');
    }
}
