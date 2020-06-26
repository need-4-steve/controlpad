<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CashTypeBackfill extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('UPDATE orders AS o JOIN cash_types AS ct ON ct.order_id = o.id SET o.cash_type = CASE WHEN ct.type = "name" THEN "Other" ELSE ct.type END WHERE o.cash_type IS NULL');
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
