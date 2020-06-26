<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCompositeKeyOnAddresses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Delete duplicates
        DB::statement("
            DELETE a2
                FROM addresses a1, addresses a2
                WHERE a1.id < a2.id
                AND a1.addressable_id = a2.addressable_id
                AND a1.addressable_type = a2.addressable_type
                AND a1.label = a2.label;
        ");
        Schema::table('addresses', function (Blueprint $table) {
            $table->unique(['addressable_id', 'addressable_type', 'label']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropUnique(['addressable_id', 'addressable_type', 'label']);
        });
    }
}
