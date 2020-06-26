<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Cartline;

class CartDeleteCascadeDeleteCartline extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Cartline::has('cart', '<', 1)->delete(); // Delete Cartlines that don't have an associated cart
        Schema::table('cartlines', function (Blueprint $table) {
            $table->integer('cart_id')->unsigned()->change();
            $table->foreign('cart_id')->references('id')->on('carts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
