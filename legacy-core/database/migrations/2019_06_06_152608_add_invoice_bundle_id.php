<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInvoiceBundleId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoice_item', function (Blueprint $table) {
            $table->dropForeign('invoice_item_item_id_foreign');
        });
        Schema::table('invoice_item', function (Blueprint $table) {
            // will be null when bundle_id is used
            $table->integer('item_id')->unsigned()->nullable(true)->change();
            $table->foreign('item_id')->references('id')->on('items'); // remove delete cascade
            $table->integer('bundle_id')->unsigned()->nullable(true);
            $table->foreign('bundle_id')->references('id')->on('bundles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoice_item', function (Blueprint $table) {
            $table->dropForeign('invoice_item_item_id_foreign');
            $table->dropForeign('invoice_item_bundle_id_foreign');
        });
        Schema::table('invoice_item', function (Blueprint $table) {
            $table->integer('item_id')->unsigned()->nullable(false)->change();
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            $table->dropColumn('bundle_id');
        });
    }
}
