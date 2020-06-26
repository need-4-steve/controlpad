<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code');
            $table->integer('owner_id');
            $table->double('amount', 12, 2)->default(0.00);
            $table->boolean('is_percent')->default(false);
            $table->string('title');
            $table->string('description');
            $table->integer('uses')->default(0);
            $table->integer('max_uses');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coupons');
    }
}
