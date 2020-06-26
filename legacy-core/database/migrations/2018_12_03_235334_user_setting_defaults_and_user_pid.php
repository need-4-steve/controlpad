<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserSettingDefaultsAndUserPid extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_setting', function (Blueprint $table) {
            $table->string('user_pid')->nullable()->index();
            $table->boolean('show_new_inventory')->default(true)->change();
            $table->boolean('show_address')->default(true)->change();
            $table->boolean('show_phone')->default(true)->change();
            $table->boolean('show_email')->default(true)->change();
            $table->string('order_confirmation_message')->default('')->change();
            $table->boolean('will_deliver')->default(false)->change();
            $table->boolean('show_location')->default(false)->change();
            $table->string('new_customer_message')->default('')->change();

        });
        DB::table('user_setting')
        ->join('users', 'users.id', '=', 'user_setting.user_id')
        ->update([
            'user_setting.user_pid' => DB::raw('users.pid'),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_setting', function (Blueprint $table) {
            $table->dropColumn(['user_pid']);
            $table->boolean('show_new_inventory')->default(null)->change();
            $table->boolean('show_address')->default(null)->change();
            $table->boolean('show_phone')->default(null)->change();
            $table->boolean('show_email')->default(null)->change();
            $table->string('order_confirmation_message')->default(null)->change();
            $table->boolean('will_deliver')->default(null)->change();
            $table->boolean('show_location')->default(null)->change();
            $table->string('new_customer_message')->default(null)->change();
        });
    }
}
