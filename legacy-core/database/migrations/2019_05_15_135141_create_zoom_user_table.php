<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateZoomUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Only install this table on MyZoomLive or maybe a sandbox
        if (env('ZOOM_API_KEY')) {
            Schema::create('zoom_user', function (Blueprint $table) {
                $table->integer('user_id')->unique();
                // Not sure if there are any issues with email being changed on account
                $table->string('email');
                $table->string('zoom_user_id');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (env('ZOOM_API_KEY')) {
            Schema::dropIfExists('zoom_user');
        }
    }
}
