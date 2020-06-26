<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddToUserSubscription extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_user', function (Blueprint $table) {
            if (!Schema::hasColumn('subscription_user', 'fail_description')) {
                $table->string('fail_description')->nullable()->change();
                $table->renameColumn('last_expiration_message_sent', 'last_fail_attempt');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscription_user', function (Blueprint $table) {
            $table->dropColumn('fail_description');
            $table->renameColumn('last_fail_attempt', 'last_expiration_message_sent');
        });
    }
}
