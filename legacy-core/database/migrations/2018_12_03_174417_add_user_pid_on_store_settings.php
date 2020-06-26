<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserPidOnStoreSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->string('user_pid')->index();
        });
        DB::table('store_settings')
        ->join('users', 'users.id', '=', 'store_settings.user_id')
        ->update([
            'store_settings.user_pid' => DB::raw('users.pid'),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn(['user_pid']);
        });
    }
}
