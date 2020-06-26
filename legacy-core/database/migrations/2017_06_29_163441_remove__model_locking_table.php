<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveModelLockingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::dropIfExists('model_locks');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('model_locks', function (Blueprint $table) {
            $table->increments('id');
            $table->morphs('model');
            $table->string('token');
            $table->timestamp('locked_until');
            $table->unsignedInteger('user_id')->nullable();
            $table->timestamps();
        });
    }
}
