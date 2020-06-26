<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGatewayToCardToken extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('card_token', function (Blueprint $table) {
            $table->string('gateway_customer_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('card_token', 'gateway_customer_id')) {
            Schema::table('card_token', function (Blueprint $table) {
                $table->dropColumn('gateway_customer_id');
            });
        }
    }
}
