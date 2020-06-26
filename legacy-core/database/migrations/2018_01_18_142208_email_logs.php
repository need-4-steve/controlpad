<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EmailLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_log', function (Blueprint $table) {
            $table->increments('id');
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->string('subject');
            $table->text('body');
            $table->text('headers')->nullable();
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
        Schema::drop('email_log');
    }
}
