<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPricesToItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->double('wholesale_price', 8, 2)->nullable()->default(null);
            $table->double('retail_price', 8, 2)->nullable()->default(null);
            $table->double('premium_price', 8, 2)->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('wholesale_price');
            $table->dropColumn('retail_price');
            $table->dropColumn('premium_price');
        });
    }
}
