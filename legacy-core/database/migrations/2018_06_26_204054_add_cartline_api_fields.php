<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCartlineApiFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cartlines', function (Blueprint $table) {
            $table->unsignedInteger('item_id')->nullable()->change();
            $table->string('pid', 25)->nullable();
            $table->unsignedInteger('bundle_id')->nullable();
            $table->string('inventory_owner_pid')->nullable();
            $table->string('bundle_name')->nullable();
            $table->string('tax_class')->nullable();
            $table->text('items')->nullable();
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
            $table->integer('item_id')->nullable(false)->change();
            $table->dropColumn('pid');
            $table->dropColumn('bundle_id');
            $table->dropColumn('bundle_name');
            $table->dropColumn('inventory_owner_pid');
            $table->dropColumn('tax_class');
            $table->dropColumn('items');
        });
    }
}
