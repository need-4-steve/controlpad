<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexToSubscriptionReceipts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_receipts', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('subscription_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscription_receipts', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['subscription_id']);
        });
    }
}
