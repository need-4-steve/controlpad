<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemovePaymentColumnsFromSubscriptionUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_user', function (Blueprint $table) {
            $table->dropColumn('transaction_id');
            $table->dropColumn('card_type');
            $table->dropColumn('card_digits');
            $table->dropColumn('card_expired');
            $table->boolean('used_free_trial')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscription_user', function (Blueprint $table) {
            $table->string('transaction_id');
            $table->string('card_type');
            $table->string('card_digits');
            $table->boolean('card_expired');
            $table->dropColumn('used_free_trial');
        });
    }
}
