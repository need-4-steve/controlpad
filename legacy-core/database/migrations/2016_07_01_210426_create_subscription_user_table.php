<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubscriptionUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription_user', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('subscription_id');
            $table->integer('user_id');
            $table->boolean('auto_renew')->default(true);
            $table->dateTime('ends_at')->nullable();
            $table->dateTime('last_expiration_message_sent');
            $table->timestamp('disabled_at')->nullable();
            $table->string('transaction_id');
            $table->string('card_type');
            $table->string('card_digits');
            $table->boolean('card_expired');
            $table->string('fail_description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscription_user');
    }
}
