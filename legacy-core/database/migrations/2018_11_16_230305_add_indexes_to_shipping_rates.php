<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexesToShippingRates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shipping_rates', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('user_pid');
            $table->index('amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shipping_rates', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['user_pid']);
            $table->dropIndex(['amount']);
        });
    }
}
