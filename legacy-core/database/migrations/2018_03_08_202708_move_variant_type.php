<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MoveVariantType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('variants', function (Blueprint $table) {
            $table->dropColumn('type');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->string('variant_label', 255)->default('');
        });
        DB::table('products')->update(['variant_label' => 'Print']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('variants', function (Blueprint $table) {
            $table->string('type', 255)->default('');
        });
        DB::table('variants')->update(['type' => 'Print']);
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('variant_label');
        });
    }
}
