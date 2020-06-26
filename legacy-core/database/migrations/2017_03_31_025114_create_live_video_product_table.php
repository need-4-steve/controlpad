<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLiveVideoProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('live_video_product', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('live_video_id');
            $table->integer('product_id');
            $table->boolean('show');
            $table->integer('order')->nullable();
            $table->double('discount_amount', 8, 2)->nullable();
            $table->string('discount_type')->nullable();
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
        Schema::dropIfExists('live_video_product');
    }
}
