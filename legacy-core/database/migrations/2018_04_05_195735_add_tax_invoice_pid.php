<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTaxInvoicePid extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->string('tax_invoice_pid', 25)->nullable();
        });
        Schema::table('subscription_receipts', function (Blueprint $table) {
            $table->string('tax_invoice_pid', 25)->nullable();
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
            $table->dropColumn('tax_invoice_pid');
        });
        Schema::table('subscription_receipts', function (Blueprint $table) {
            $table->dropColumn('tax_invoice_pid');
        });
    }
}
