<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomEmail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_emails', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->unique();
            $table->string('greeting');
            $table->text('content_1');
            $table->text('content_2');
            $table->text('content_3');
            $table->string('signature');
            $table->boolean('send_email')->default(true);
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
        Schema::dropIfExists('custom_emails');
    }
}
