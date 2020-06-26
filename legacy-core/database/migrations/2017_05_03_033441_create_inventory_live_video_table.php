<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInventoryLiveVideoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_live_video', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('live_video_id')->unsigned();
            $table->integer('inventory_id')->unsigned();
            $table->integer('sale_quantity');
            $table->double('discount_amount', 5, 2);
            $table->boolean('discount_is_percent')->default(true);
            $table->timestamps();

            // cross-table references
            $table->foreign('live_video_id')->references('id')->on('live_videos');
            $table->foreign('inventory_id')->references('id')->on('inventories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_live_video');
    }
}
