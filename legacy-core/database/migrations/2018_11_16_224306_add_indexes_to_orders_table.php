<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexesToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $doctrineTable = $sm->listTableDetails('orders');
            if (!$doctrineTable->hasIndex('orders_seller_pid_index')) {
                $table->index('seller_pid');
            }
            if (!$doctrineTable->hasIndex('orders_buyer_pid_index')) {
                $table->index('buyer_pid');
            }
            $table->index('type_id');
            $table->index('paid_at');
            $table->index('pid');
            $table->index('confirmation_code');
            $table->index('coupon_id');
            $table->index('cash_type');
            $table->index('payment_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['seller_pid']);
            $table->dropIndex(['buyer_pid']);
            $table->dropIndex(['type_id']);
            $table->dropIndex(['paid_at']);
            $table->dropIndex(['pid']);
            $table->dropIndex(['confirmation_code']);
            $table->dropIndex(['coupon_id']);
            $table->dropIndex(['cash_type']);
            $table->dropIndex(['payment_type']);
        });
    }
}
