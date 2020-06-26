<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration
{

    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sponsor_id')->nullable()->unsigned()->index();
            $table->string('public_id', 25)->nullable()->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->integer('role_id')->default(3); //Defaults to Customer
            $table->integer('seller_type_id')->nullable();
            $table->string('mobile_key')->nullable()->unique();
            $table->timestamps();
            $table->timestamp('disabled_at')->nullable();
            $table->rememberToken();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
