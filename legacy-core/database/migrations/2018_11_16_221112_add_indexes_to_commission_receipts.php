<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexesToCommissionReceipts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('commission_receipts', function (Blueprint $table) {
            $table->index('order_id');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('commission_receipts', function (Blueprint $table) {
            $table->dropIndex(['order_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['created_at']);
        });
    }
}
