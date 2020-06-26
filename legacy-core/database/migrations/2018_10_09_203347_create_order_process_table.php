<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderProcessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_process', function (Blueprint $table) {
            $table->unsignedInteger('order_id');
            $table->timestamp('paid_at')->nullable()->default(null);
            $table->timestamp('emails_sent')->nullable()->default(null);
            $table->timestamp('taxes_committed')->nullable()->default(null);
            $table->string('tax_invoice_pid', 25)->nullable(true);
            $table->timestamp('commissions_sent')->nullable()->default(null);
            $table->integer('commission_status_id')->default(1);

            $table->foreign('order_id')->references('id')->on('orders');
            $table->index('paid_at');
            $table->index('emails_sent');
            $table->index('taxes_committed');
            $table->index('commissions_sent');
            $table->index('commission_status_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->timestamp('emails_sent')->nullable()->default(null);
            $table->index('emails_sent');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_process');
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('emails_sent');
        });
    }
}
