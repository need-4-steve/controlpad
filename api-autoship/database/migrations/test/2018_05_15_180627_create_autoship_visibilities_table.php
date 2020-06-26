<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;

class CreateAutoshipVisibilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('autoship_visibilities', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('description');
            $table->timestamps();
            $table->softDeletes();
        });
        DB::table('autoship_visibilities')->insert([
            [
                'id' => 3,
                'name' => 'Customer',
                'description' => 'Someone who has purchased a product or service.'
            ],
            [
                'id' => 5,
                'name' => 'Rep',
                'description' => 'A fully-featured member and representative. Can only access features related to their sales and resources.'
            ],
            [
                'id' => 7,
                'name' => 'Admin',
                'description' => 'An administrative account with access to almost all features.'
            ],
            [
                'id' => 8,
                'name' => 'Superadmin',
                'description' => 'An administrative account with access to all features.'
            ],
        ]);
        $now = Carbon::now();
        DB::table('autoship_visibilities')->update([
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('autoship_visibilities');
    }
}
