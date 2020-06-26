<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_setting', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->boolean('show_new_inventory');
            $table->boolean('show_address');
            $table->boolean('show_phone');
            $table->boolean('show_email');
            $table->boolean('show_location');
            $table->boolean('will_deliver');
            $table->string('new_customer_message');
            $table->string('order_confirmation_message');
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
        Schema::dropIfExists('user_setting');
    }
}
