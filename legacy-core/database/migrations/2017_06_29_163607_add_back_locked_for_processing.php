<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBackLockedForProcessing extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->boolean('locked_for_processing');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('inventories', 'locked_for_processing')) {
            Schema::table('inventories', function (Blueprint $table) {
                $table->dropColumn('locked_for_processing');
            });
        }
    }
}
