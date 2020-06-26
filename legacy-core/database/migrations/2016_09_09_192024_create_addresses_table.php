<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddressesTable extends Migration
{
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('address_1');
            $table->string('address_2');
            $table->string('city', 35);
            $table->string('state', 2);
            $table->morphs('addressable');
            $table->string('zip', 10);
            $table->string('label');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('addresses');
    }
}
