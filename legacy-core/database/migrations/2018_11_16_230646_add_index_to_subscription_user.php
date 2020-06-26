<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexToSubscriptionUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_user', function (Blueprint $table) {
            $table->index('subscription_id');
            $table->index('ends_at');
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
            $table->dropIndex(['subscription_id']);
            $table->dropIndex(['ends_at']);
        });
    }
}
