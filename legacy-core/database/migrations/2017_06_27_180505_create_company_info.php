<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_info', function (Blueprint $table) {
             $table->increments('id');
             $table->integer('user_id')->unsigned()->nullable();
             $table->string('name')->nullable();
             $table->string('ein')->nullable();
             $table->timestamps();

             $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company_info');
    }
}
