<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAutoshipSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('autoship_subscriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('pid')->index();
            $table->integer('autoship_plan_id')->index()->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->dateTime('disabled_at')->nullable();
            $table->dateTime('next_billing_at')->useCurrent()->index();
            $table->string('duration')->references('name')->on('autoship_durations');
            $table->integer('frequency');
            $table->float('percent_discount', 8, 2)->default(0);
            $table->json('discounts')->nullable();
            $table->integer('cycle')->default(1);
            $table->boolean('free_shipping')->default(false);
            // Cart related fields
            $table->string('seller_pid')->index();
            $table->string('buyer_pid')->index();
            $table->string('buyer_first_name')->nullable()->index();
            $table->string('buyer_last_name')->nullable()->index();
            $table->string('inventory_user_pid')->default("COLUMN VALUE OF seller_pid");
            $table->string('type')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('autoship_subscriptions');
    }
}
