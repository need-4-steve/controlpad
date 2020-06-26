<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AutoshipSubLineItemIdNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('autoship_subscription_lines', function (Blueprint $table) {
            $table->integer('item_id')->nullable(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('autoship_subscription_lines', function (Blueprint $table) {
            $table->integer('item_id')->nullable(false)->change();
        });
    }
}
