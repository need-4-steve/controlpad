<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserSubscriptionDefaults extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_user', function (Blueprint $table) {
            // New instances don't have the renamed column
            if (Schema::hasColumn('subscription_user', 'last_expiration_message_sent')) {
                $table->renameColumn('last_expiration_message_sent', 'last_fail_attempt');
            }
        });
        Schema::table('subscription_user', function (Blueprint $table) {
            $table->dateTime('last_fail_attempt')->nullable()->change();
            $table->string('fail_description')->nullable()->change();
            $table->integer('subscription_id')->nullable()->change();
            $table->integer('user_id')->nullable()->change();
        });
        DB::table('subscription_user')->where('last_fail_attempt', '0000-00-00 00:00:00')->update(['last_fail_attempt' => null]);
    }
     /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('subscription_user')->where('last_fail_attempt', null)->update(['last_fail_attempt' => '0000-00-00 00:00:00']);
        Schema::table('subscription_user', function (Blueprint $table) {
            $table->string('last_fail_attempt')->nullable(false)->change();
            $table->string('fail_description')->nullable(false)->change();
            $table->integer('subscription_id')->nullable(false)->change();
            $table->integer('user_id')->nullable(false)->change();
        });
    }
}
