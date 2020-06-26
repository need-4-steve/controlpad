<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAnnouncementsTable extends Migration
{

    public function up()
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('url');
            $table->text('description');
            $table->text('body');
            $table->dateTime('publish_date')->nullable();
            $table->integer('postCategory_id');
            $table->boolean('public');
            $table->boolean('reps');
            $table->boolean('customers');
            $table->boolean('disabled');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('announcements');
    }
}
