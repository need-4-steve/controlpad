<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaxConnectionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tax_connections', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('merchant_id', 64);
            $table->string('type', 16);
            $table->text('credentials');
            $table->boolean('active');
            $table->boolean('sandbox');
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
        Schema::dropIfExists('tax_connections');
    }
}
