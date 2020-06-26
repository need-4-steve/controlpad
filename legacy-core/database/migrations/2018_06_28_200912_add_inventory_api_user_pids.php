<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInventoryApiUserPids extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->string('user_pid', 25)->nullable();
            $table->string('owner_pid', 25)->nullable();
        });
        Schema::table('products', function (Blueprint $table) {
            $table->string('user_pid', 25)->nullable();
        });
        Schema::table('bundles', function (Blueprint $table) {
            $table->string('user_pid', 25)->nullable();
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
            $table->dropColumn(['user_pid', 'owner_pid']);
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('user_pid');
        });
        Schema::table('bundles', function (Blueprint $table) {
            $table->dropColumn('user_pid');
        });
    }
}
