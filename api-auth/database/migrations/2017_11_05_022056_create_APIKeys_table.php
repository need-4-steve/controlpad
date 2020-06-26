<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAPIKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->increments('id');
            $table->string('secret', 64)->unique();
            $table->string('app_id', 32)->unique();
            $table->string('app_name');
            $table->date('expires_at')->index();
            $table->date('deleted_at')->index()->nullable();
            $table->integer('tenant_id')->unsigned();
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('APIKeys');
    }
}
