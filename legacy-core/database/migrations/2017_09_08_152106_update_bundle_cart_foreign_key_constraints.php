<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateBundleCartForeignKeyConstraints extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // delete records in the bundle_cart pivot tjat doesn't have an associated record in carts or bundles
        DB::table('bundle_cart')->whereRaw('cart_id NOT IN (SELECT id FROM carts)')->delete(); 
        DB::table('bundle_cart')->whereRaw('bundle_id NOT IN (SELECT id FROM bundles where deleted_at = NULL)')->delete();
        Schema::table('bundle_cart', function (Blueprint $table) {
            $table->integer('cart_id')->unsigned()->change();
            $table->integer('bundle_id')->unsigned()->change();
            $table->foreign('cart_id')->references('id')->on('carts')->onDelete('cascade');
            $table->foreign('bundle_id')->references('id')->on('bundles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
