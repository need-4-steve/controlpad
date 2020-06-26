<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CheckoutPreReleaseFixes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('checkouts', function (Blueprint $table) {
            $table->boolean('couponable')->default(1);
            $table->unsignedInteger('invoice_id')->nullable();
            $table->boolean('tax_exempt')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('checkouts', function (Blueprint $table) {
            $table->dropColumn('couponable');
            $table->dropColumn('invoice_id');
            $table->dropColumn('tax_exempt');
        });
    }
}
