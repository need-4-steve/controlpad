<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InventoryIndexingFix extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropIndex(['disabled_at']);
            $table->dropIndex(['quantity_available']);
            $table->index(['quantity_available', 'disabled_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropIndex(['quantity_available', 'disabled_at']);
            $table->index(['disabled_at']);
            $table->index(['quantity_available']);
        });
    }
}
