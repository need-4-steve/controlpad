<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CheckoutApiPrep2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedInteger('shipping_rate_id')->nullable();
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->string('cash_type', 32)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('shipping_rate_id');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('cash_type');
        });
    }
}
