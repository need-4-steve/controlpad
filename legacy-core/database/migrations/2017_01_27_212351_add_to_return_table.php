<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddToReturnTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('returns', function (Blueprint $table) {
            $table->integer('return_quantity');
            $table->double('amount_returned', 7, 2);
            $table->integer('auth_user_id');
            $table->string('transaction_id')->nullable();
            $table->string('type');
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
            $table->dropColumn('return_quantity');
            $table->dropColumn('amount_returned');
            $table->dropColumn('auth_user_id');
            $table->dropColumn('transaction_id');
            $table->dropColumn('type');
        });
    }
}
