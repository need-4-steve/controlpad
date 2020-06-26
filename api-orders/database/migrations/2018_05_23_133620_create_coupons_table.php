<?php

use Illuminate\Support\Facades\Schema;
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
            $table->string('code', 255);
            $table->string('owner_pid', 25);
            $table->decimal('amount', 12, 2);
            $table->boolean('is_percent');
            $table->string('title', 255);
            $table->string('description', 255)->default('');
            $table->string('type', 64);
            $table->integer('uses')->default(0);
            $table->integer('max_uses');
            $table->timestamp('expires_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('order_coupons', function (Blueprint $table) {
            $table->unsignedInteger('coupon_id');
            $table->unsignedInteger('order_id');

            $table->foreign('coupon_id')->references('id')->on('coupons');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
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
        Schema::dropIfExists('applied_coupons');
    }
}
