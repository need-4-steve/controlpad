<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexesToOrderlines extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orderlines', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $doctrineTable = $sm->listTableDetails('orderlines');
            if ($doctrineTable->hasIndex('order_id')) {
                $table->dropIndex('order_id');
            }
            if (! $doctrineTable->hasIndex('orderlines_order_id_index')) {
                $table->index('order_id');
            }
            if (! $doctrineTable->hasIndex('orderlines_bundle_id_index')) {
                $table->index('bundle_id');
            }
            if (! $doctrineTable->hasIndex('orderlines_item_id_index')) {
                $table->index('item_id');
            }
            if (! $doctrineTable->hasIndex('orderlines_inventory_owner_id_index')) {
                $table->index('inventory_owner_id');
            }
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
            $table->dropIndex(['order_id']);
            $table->dropIndex(['bundle_id']);
            $table->dropIndex(['item_id']);
            $table->dropIndex(['inventory_owner_id']);
        });
    }
}
