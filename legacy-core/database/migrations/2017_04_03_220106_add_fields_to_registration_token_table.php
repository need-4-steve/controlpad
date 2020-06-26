<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToRegistrationTokenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('registration_tokens', function (Blueprint $table) {
            $table->string('address_1');
            $table->string('address_2');
            $table->string('city');
            $table->string('state');
            $table->string('zip');
            $table->string('phone');
            $table->string('public_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('registration_tokens', function (Blueprint $table) {
            $table->dropColumn('address_1');
            $table->dropColumn('address_2');
            $table->dropColumn('city');
            $table->dropColumn('state');
            $table->dropColumn('zip');
            $table->dropColumn('phone');
            $table->dropColumn('public_id');
        });
    }
}
