<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ProductVideo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_video', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('live_video_product_id')->unsigned();
            $table->integer('live_video_id')->unsigned();
            $table->foreign('live_video_product_id')->references('id')->on('live_video_products')->onDelete('cascade');
            $table->foreign('live_video_id')->references('id')->on('live_videos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_video');
    }
}
