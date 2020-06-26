<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('party_id');
        });
        Schema::table('orderlines', function (Blueprint $table) {
            $table->integer('event_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->integer('party_id')->nullable();
        });
        Schema::table('orderlines', function (Blueprint $table) {
            $table->dropColumn('event_id');
        });
    }
}
