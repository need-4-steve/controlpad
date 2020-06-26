<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateOrderlinesWithForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table('orderlines', function (Blueprint $table) {
        //     $table->integer('order_id')->unsigned()->index()->change();
        //     $table->foreign('order_id')->references('id')->on('orders');

        //     $table->integer('item_id')->unsigned()->index()->change();
        //     $table->foreign('item_id')->references('id')->on('items');
            
        //     $table->integer('inventory_owner_id')->unsigned()->index()->change();
        //     $table->foreign('inventory_owner_id')->references('id')->on('users');
            
        //     $table->integer('bundle_id')->unsigned()->index()->nullable()->change();
        //     $table->foreign('bundle_id')->references('id')->on('bundles');
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orderlines', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropForeign(['item_id']);
            $table->dropForeign(['inventory_owner_id']);
            $table->dropForeign(['bundle_id']);
        });
    }
}
