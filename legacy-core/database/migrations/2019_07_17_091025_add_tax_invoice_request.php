<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTaxInvoiceRequest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tax_invoices', function (Blueprint $table) {
            $table->decimal('discount', 8, 2)->default(0.00)->after('subtotal');
            $table->decimal('shipping', 8, 2)->default(0.00)->after('discount');
            $table->mediumText('request')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tax_invoices', function (Blueprint $table) {
            $table->dropColumn(['request', 'discount', 'shipping']);
        });
    }
}
