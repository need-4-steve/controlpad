<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReturnTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('return_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('return_id')->unsigned();
            $table->integer('auth_user_id')->unsigned();
            $table->double('amount', 8, 2);
            $table->string('transaction_id');
            $table->string('type');
            $table->timestamps();

            $table->foreign('return_id')->references('id')->on('returns');
            $table->foreign('auth_user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('return_transactions');
    }
}
