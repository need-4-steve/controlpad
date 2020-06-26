<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexesToCarts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->index('pid');
            $table->index('buyer_pid');
            $table->index('seller_pid');
            $table->index('inventory_user_pid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropIndex(['pid']);
            $table->dropIndex(['buyer_pid']);
            $table->dropIndex(['seller_pid']);
            $table->dropIndex(['inventory_user_pid']);
        });
    }
}
