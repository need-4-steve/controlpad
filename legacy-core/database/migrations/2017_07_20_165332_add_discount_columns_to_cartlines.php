<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDiscountColumnsToCartlines extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cartlines', function (Blueprint $table) {
            $table->double('discount', 7, 2)->default(0);
            $table->integer('discount_type_id')->unsigned()->index()->nullable();
            $table->foreign('discount_type_id')->references('id')->on('discount_types')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cartlines', function (Blueprint $table) {
            $table->dropColumn(['discount_amount', 'discount_type_id']);
        });
    }
}
