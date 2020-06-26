<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;

class TaxApiSetup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Merchants can have their own connection in theory
        Schema::create('tax_connections', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('merchant_id', 64); // Seller pid
            $table->string('type', 16); // avalara, exactor, sovos, tax-jar, mock
            $table->text('credentials'); // will be encrypted and base64_encode
            $table->boolean('active');
            $table->boolean('sandbox');
            $table->timestamps();
        });

        Schema::create('tax_invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('pid', 25);
            $table->unsignedBigInteger('tax_connection_id');
            $table->string('merchant_id', 64); // Seller pid
            $table->decimal('subtotal', 8, 2);
            $table->decimal('tax', 8, 2);
            $table->string('type', 16); // sale,use,transfer,refund,refund-full
            $table->string('reference_id')->nullable(); // External id from service
            $table->string('order_pid', 64)->nullable();
            $table->string('origin_pid', 25)->nullable(); // tax_invoice_pid a refund originates from
            $table->dateTime('committed_at')->nullable();
            $table->timestamps();

            $table->unique('pid');
            $table->foreign('tax_connection_id')->references('id')->on('tax_connections');
            $table->foreign('origin_pid')->references('pid')->on('tax_invoices');
        });
        Schema::table('carts', function (Blueprint $table) {
            $table->integer('user_id')->nullable(true)->change();
            $table->string('uid', 25)->nullable(true)->change();
            $table->decimal('subtotal_price', 8, 2)->nullable(true)->change();
            $table->decimal('total_tax', 8, 2)->nullable(true)->change();
            $table->decimal('total_discount', 8, 2)->nullable(true)->change();
            $table->decimal('total_price', 8, 2)->nullable(true)->change();
            $table->decimal('total_shipping', 8, 2)->nullable(true)->change();
            $table->integer('shipping_rate_id')->nullable(true)->change();
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->string('tax_invoice_pid', 25)->nullable(true);
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
        Schema::dropIfExists('tax_connections');
        Schema::table('carts', function (Blueprint $table) {
            $table->integer('user_id')->nullable(false)->change();
            $table->string('uid', 25)->nullable(false)->change();
            $table->decimal('subtotal_price', 8, 2)->nullable(false)->change();
            $table->decimal('total_tax', 8, 2)->nullable(false)->change();
            $table->decimal('total_discount', 8, 2)->nullable(false)->change();
            $table->decimal('total_price', 8, 2)->nullable(false)->change();
            $table->decimal('total_shipping', 8, 2)->nullable(false)->change();
            $table->integer('shipping_rate_id')->nullable(false)->change();
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('tax_invoice_pid');
        });
    }
}
