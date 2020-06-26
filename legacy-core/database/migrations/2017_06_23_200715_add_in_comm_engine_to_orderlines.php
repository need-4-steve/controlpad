<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInCommEngineToOrderlines extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orderlines', function (Blueprint $table) {
            $table->boolean('in_comm_engine')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orderlines', function (Blueprint $table) {
            $table->dropColumn('in_comm_engine');
        });
    }
}
