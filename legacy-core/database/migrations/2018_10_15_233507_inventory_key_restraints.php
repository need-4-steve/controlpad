<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InventoryKeyRestraints extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Delete inventories from a product made by a rep that somehow corporate has inventory for.
        $query1 = 'DELETE inventories FROM inventories '.
            'JOIN items ON items.id = inventories.item_id '.
            'JOIN products ON products.id = items.product_id '.
            'WHERE inventories.user_id = 1 '.
            'AND products.user_id != 1';
        DB::delete($query1);

        // Delete inventories that currently don't have a valid item_id to go with it.
        $query2 = 'DELETE inventories FROM inventories '.
            'LEFT JOIN items ON items.id = inventories.item_id '.
            'WHERE items.id IS NULL';
        DB::delete($query2);

        // Set the foreign key restraints on the database for items and inventory.
        Schema::table('inventories', function (Blueprint $table) {
            $table->integer('item_id')->unsigned()->change();
            $table->foreign('item_id')->references('id')->on('items');
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
            $table->dropForeign('inventories_item_id_foreign');
        });
    }
}
