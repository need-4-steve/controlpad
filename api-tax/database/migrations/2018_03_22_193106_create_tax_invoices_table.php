<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaxInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tax_invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('pid', 25);
            $table->unsignedBigInteger('tax_connection_id');
            $table->string('merchant_id', 64);
            $table->decimal('subtotal', 8, 2);
            $table->decimal('tax', 8, 2);
            $table->string('type', 16);
            $table->string('reference_id')->nullable();
            $table->string('order_pid', 64)->nullable();
            $table->string('origin_pid', 25)->nullable();
            $table->dateTime('committed_at')->nullable();
            $table->timestamps();

            $table->unique('pid');
            $table->foreign('tax_connection_id')->references('id')->on('tax_connections');
            $table->foreign('origin_pid')->references('pid')->on('tax_invoices');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tax_invoices');
    }
}
