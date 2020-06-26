<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;

class CreateAutoshipAttemptStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('autoship_attempt_status', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });
        $now = Carbon::now();
        DB::table('autoship_attempt_status')->insert([
            ['name' => 'success',           'created_at' => $now, 'updated_at' => $now],
            ['name' => 'failure',           'created_at' => $now, 'updated_at' => $now],
            ['name' => 'skipped',           'created_at' => $now, 'updated_at' => $now],
            ['name' => 'error',             'created_at' => $now, 'updated_at' => $now],
            ['name' => 'inventory failure', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('autoship_attempt_status');
    }
}
