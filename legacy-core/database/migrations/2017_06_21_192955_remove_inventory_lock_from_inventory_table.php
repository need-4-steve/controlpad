<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveInventoryLockFromInventoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('inventories', 'locked_for_processing')) {
            Schema::table('inventories', function (Blueprint $table) {
                $table->dropColumn('locked_for_processing');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // no changes on down migration; don't want to add the column if it doesn't exist
    }
}
