<?php

use Illuminate\Database\Migrations\Migration;

class OrderProcessBackfillTax extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('UPDATE order_process AS op JOIN tax_invoices AS ti ON ti.pid = op.tax_invoice_pid SET op.taxes_committed = ti.committed_at');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Nothing
    }
}
