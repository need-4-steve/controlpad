<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAutoshipDurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('autoship_durations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->timestamps();
            $table->softDeletes();
        });
        $now = date('Y-m-d h:i:s');
        DB::table('autoship_durations')->insert([
            ['name' => 'Days',      'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Weeks',     'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Months',    'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Quarters',  'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Years',     'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('autoship_durations');
    }
}
