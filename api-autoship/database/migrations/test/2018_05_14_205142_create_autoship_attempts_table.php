<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAutoshipAttemptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('autoship_attempts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('pid')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('autoship_subscription_id')->index()->references('id')->on('autoship_subscriptions');
            $table->integer('subscription_cycle')->index();
            $table->text('description')->nullable();
            $table->string('status')->index()->references('name')->on('autoship_attempt_status');
            $table->string('order_pid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('autoship_attempts');
    }
}
