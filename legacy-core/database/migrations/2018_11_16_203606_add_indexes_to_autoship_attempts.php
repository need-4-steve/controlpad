<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexesToAutoshipAttempts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('autoship_attempts', function (Blueprint $table) {
            $table->index('order_pid');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('autoship_attempts', function (Blueprint $table) {
            $table->dropIndex(['order_pid']);
            $table->dropIndex(['created_at']);
        });
    }
}
