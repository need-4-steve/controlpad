<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEventStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
            $table->string('status')->default('open');
            $table->integer('items_limit')->nullable();
            $table->integer('items_purchased')->default(0);
            $table->string('img')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('status')->default('open');
            $table->dropColumn('items_limit');
            $table->dropColumn('items_purchased');
            $table->dropColumn('deleted_at');
        });
    }
}
