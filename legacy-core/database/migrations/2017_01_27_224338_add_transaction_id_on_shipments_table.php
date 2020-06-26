<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTransactionIdOnShipmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->string('transaction_id');
            $table->double('amount', 8, 2);
            $table->double('markup', 8, 2);
            $table->double('total_price', 8, 2);
            $table->integer('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn('transaction_id');
            $table->dropColumn('amount');
            $table->dropColumn('markup');
            $table->dropColumn('total_price');
            $table->dropColumn('user_id');
        });
    }
}
