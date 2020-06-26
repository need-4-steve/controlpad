<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexesToBlacklisted extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('blacklisted', function (Blueprint $table) {
            $table->index('name');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('blacklisted', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['category']);
        });
    }
}
