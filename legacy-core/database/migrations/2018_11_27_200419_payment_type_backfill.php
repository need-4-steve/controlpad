<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PaymentTypeBackfill extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Orders prior to the order api updating the payment type field, some inconsistency in saving 'cash' field, so checking personal use and $0 orders as well.
        DB::statement('UPDATE orders SET payment_type = CASE WHEN cash = 1 OR type_id = 10 OR total_price = 0 THEN "cash" ELSE "credit-card" END WHERE payment_type IS NULL');
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
