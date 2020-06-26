<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderlinesCheckoutApiFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orderlines', function (Blueprint $table) {
            $table->string('pid', 25)->nullable();
            $table->string('inventory_owner_pid', 25)->nullable();
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
        Schema::table('orderlines', function (Blueprint $table) {
            $table->dropColumn('pid');
            $table->dropColumn('inventory_owner_pid');
            $table->dropColumn('items');
        });
    }
}
