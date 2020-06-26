<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->increments('id');
            $table->string('sponsor_id')->index();
            $table->string('host_id')->nullable()->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('img');
            $table->string('location')->nullable();
            $table->string('host_name')->nullable();
            $table->dateTime('sale_start')->nullable()->index();
            $table->dateTime('sale_end')->nullable()->index();
            $table->dateTime('date')->nullable()->index();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE events ADD FULLTEXT events_search_index (name, description)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
}
