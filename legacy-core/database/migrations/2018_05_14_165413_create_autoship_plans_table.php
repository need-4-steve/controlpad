<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAutoshipPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('autoship_plans', function (Blueprint $table) {
            $table->increments('id');
            $table->string('pid')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->dateTime('disabled_at')->nullable();
            $table->string('title')->nullable()->index();
            $table->string('description')->nullable();
            $table->float('discount', 8, 2)->default(0);
            $table->json('discounts')->nullable();
            $table->string('duration');
            $table->foreign('duration')->references('name')->on('autoship_durations');
            $table->integer('frequency');
            $table->boolean('free_shipping')->default(false);
        });
        DB::statement('ALTER TABLE autoship_plans ADD FULLTEXT full(description)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('autoship_plans');
    }
}
