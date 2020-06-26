<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTaxInvoiceTable extends Migration
{

    public function up()
    {
        Schema::create('tax_invoice', function (Blueprint $table) {
            $table->increments('id');
            $table->string('transaction_id');
            $table->timestamp('transaction_date');
            $table->timestamp('sale_date');
            $table->string('currency_code');
            $table->string('tax_class');
            $table->string('tax_direction');
            $table->decimal('gross_amount');
            $table->decimal('total_tax_amount');
            $table->integer('taxable_id');
            $table->string('taxable_type');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tax_invoice');
    }
}
