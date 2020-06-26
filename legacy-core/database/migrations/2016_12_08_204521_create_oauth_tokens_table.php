<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOauthTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oauth_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('driver_id');
            $table->string('email')->unique();
            $table->string('token')->unique();
            $table->string('refresh_token')->unique()->nullable();
            $table->timestamp('expires_at')->nullable();
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
        Schema::dropIfExists('oauth_tokens');
    }
}
