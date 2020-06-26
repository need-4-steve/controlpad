<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddResalableToProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('resellable')->default(0);
        });
        DB::table('products')->where('type_id', '=', 1)->update(['resellable' => 1]);
        DB::table('product_type')->where('id', '=', 6)->update(['name' => 'Business Tools']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['resellable']);
        });
        DB::table('product_type')->where('id', '=', 6)->update(['name' => 'Non-Resellable']);
    }
}
