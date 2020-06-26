<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEmailMessagesTable extends Migration
{

    public function up()
    {
        Schema::create('email_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sender_id');
            $table->integer('recipient_id');
            $table->text('subject');
            $table->text('body');
            $table->string('messagable_type');
            $table->integer('messagable_id');
            $table->boolean('disabled');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('email_messages');
    }
}
