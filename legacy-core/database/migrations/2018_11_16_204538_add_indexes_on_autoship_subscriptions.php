<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexesOnAutoshipSubscriptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('autoship_subscriptions', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('inventory_user_pid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('autoship_subscriptions', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['inventory_user_pid']);
        });
    }
}
