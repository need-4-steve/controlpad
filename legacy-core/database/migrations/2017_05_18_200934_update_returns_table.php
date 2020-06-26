<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateReturnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('returns', function (Blueprint $table) {
            // modify the existing columns to be unsigned so we can add foreign key constraints
            $table->integer('order_id')->unsigned()->nullable()->change();
            $table->integer('return_status_id')->unsigned()->nullable()->change();
            $table->integer('user_id')->unsigned()->nullable()->change();

            // new columns
            $table->integer('initiator_user_id')->unsigned()->nullable();

            // drop old columns
            $table->dropColumn([
                'orderline_id', 'auth_user_id', 'return_quantity',
                'transaction_id', 'type', 'amount_returned',
                'comments',
            ]);

            // add foreign key constraints to new and existing columns
            $table->foreign('order_id')->references('id')->on('orders');
            $table->foreign('return_status_id')->references('id')->on('return_statuses');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('initiator_user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('returns', function (Blueprint $table) {
            $table->integer('orderline_id');
            $table->integer('auth_user_id');
            $table->integer('return_quantity');
            $table->string('transaction_id');
            $table->string('type');
            $table->double('amount_returned', 7, 2);

            $table->dropForeign([
                'order_id', 'initiator_user_id',
                'return_reason_id', 'return_status_id',
            ]);

            $table->renameColumn('initiator_user_id', 'user_id');
        });
    }
}
