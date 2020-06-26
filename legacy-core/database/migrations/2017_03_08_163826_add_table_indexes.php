<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTableIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('expires_at');
            $table->index('disabled_at');
            $table->index('quantity_available');
            $table->index('item_id');
        });

        Schema::table('prices', function (Blueprint $table) {
            $table->index('price_type_id');
            $table->index('priceable_type');
            $table->index('priceable_id');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->index('is_default');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
