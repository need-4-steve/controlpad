<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailLog extends Migration
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
          $table->string('to');
          $table->string('from');
          $table->string('subject');
          $table->text('body');
          $table->string('org_id');
          $table->boolean('success');
          $table->string('fail_reason')->nullable;
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
        Schema::dropIfExists('email_log');
    }
}
