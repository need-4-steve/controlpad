<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLiveVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('live_videos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('oauth_driver_id');
            $table->string('video_id')->nullable();
            $table->string('thumbnail')->default('');
            $table->boolean('service_save_later')->default(false);
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
        Schema::dropIfExists('live_videos');
    }
}
