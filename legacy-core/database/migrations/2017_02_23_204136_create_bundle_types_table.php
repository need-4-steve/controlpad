<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;

class CreateBundleTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bundle_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->timestamps();
        });

        DB::table('bundle_types')->insert([
            ['name' => 'Bundle', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Fulfilled by Corporate', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bundle_types');
    }
}
