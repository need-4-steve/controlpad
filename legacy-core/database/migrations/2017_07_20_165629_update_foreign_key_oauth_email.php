<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateForeignKeyOauthEmail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oauth_tokens', function (Blueprint $table) {
            $table->string('service_email')->nullable();

            $table->dropForeign(['email']);
            $table->foreign('email')->references('email')->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('oauth_tokens', function (Blueprint $table) {
            $table->dropColumn('service_email');
        });
    }
}
