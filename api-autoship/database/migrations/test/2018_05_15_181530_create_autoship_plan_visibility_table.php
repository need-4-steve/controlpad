<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAutoshipPlanVisibilityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('autoship_plan_visibility', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('autoship_plan_id')->unsigned()->index();
            $table->integer('visibility_id')->unsigned()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('autoship_plan_visibility');
    }
}
