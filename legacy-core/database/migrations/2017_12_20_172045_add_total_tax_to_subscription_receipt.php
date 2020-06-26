<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTotalTaxToSubscriptionReceipt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_receipts', function (Blueprint $table) {
            $table->double('total_tax', 8, 2);
            $table->double('total_price', 8, 2);
            $table->boolean('taxes_committed');
        });
        DB::statement('UPDATE subscription_receipts SET total_price = subtotal_price');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscription_receipts', function (Blueprint $table) {
            $table->dropIfExists('total_tax');
            $table->dropIfExists('total_price');
            $table->dropIfExists('taxes_committed');
        });
    }
}

