<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCardToken extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('card_token', function (Blueprint $table) {
            $table->increments('id');
            $table->string('token');
            $table->integer('user_id');
            $table->string('type');
            $table->string('card_type');
            $table->string('card_digits');
            $table->string('expiration');
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
        Schema::dropIfExists('card_token');
    }
}
