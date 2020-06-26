<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\ReturnReason;

class CreateReturnReasonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('return_reasons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('keyname')->unique();
            $table->timestamps();
        });
            $seeder = new \ReturnReasonsTableSeeder;
            $seeder->run();
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('return_reasons');
    }
}
