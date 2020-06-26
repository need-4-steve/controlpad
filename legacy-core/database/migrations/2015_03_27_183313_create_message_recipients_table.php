<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMessageRecipientsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_recipients', function (Blueprint $table) {
            $table->increments('id');
            $table->string('recipientable_type');
            $table->integer('recipientable_id');
            $table->string('messagable_type');
            $table->integer('messagable_id');
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
        Schema::dropIfExists('message_recipients');
    }
}
