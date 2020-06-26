<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropCorporateColumnsOnCartlinesAndOrderlines extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cartlines', function (Blueprint $table) {
            $table->dropColumn('corporate');
        });

        Schema::table('orderlines', function (Blueprint $table) {
            $table->dropColumn('corporate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cartlines', function (Blueprint $table) {
            $table->boolean('corporate')->nullable();
        });

        Schema::table('orderlines', function (Blueprint $table) {
            $table->boolean('corporate')->nullable();
        });
    }
}
