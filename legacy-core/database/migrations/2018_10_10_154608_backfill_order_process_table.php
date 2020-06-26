<?php

use Illuminate\Database\Migrations\Migration;

class BackfillOrderProcessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement(
            'INSERT INTO order_process(order_id, paid_at, emails_sent, taxes_committed, tax_invoice_pid) '.
            'SELECT id, paid_at, NOW(), CASE WHEN taxes_committed THEN NOW() ELSE NULL END, tax_invoice_pid FROM orders'
        );
        DB::statement('UPDATE invoices SET emails_sent = created_at');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DELETE FROM order_process');
        DB::statement('UPDATE invoices SET emails_sent = NULL');
    }
}
