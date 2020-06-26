<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateOauthTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oauth_tokens', function (Blueprint $table) {
            $table->integer('user_id')->unsigned()->change();
            $table->integer('driver_id')->unsigned()->change();
            $table->renameColumn('token', 'access_token');
            $table->timestamp('issued_at')->useCurrent();
            $table->unique(['driver_id', 'user_id']);


            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('email')->references('email')->on('users');
            $table->foreign('driver_id')->references('id')->on('oauth_drivers');
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
            $table->renameColumn('access_token', 'token');
            $table->dropColumn('issued_at');
            $table->dropUnique('oauth_tokens_driver_id_user_id_unique');
            $table->dropForeign(['user_id']);
            $table->dropForeign(['email']);
            $table->dropForeign(['driver_id']);
        });
    }
}
