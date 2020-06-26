<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProductDefaults extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('type_id')->unsigned()->default(1)->change();
            $table->string('tax_class')->default('')->change();
            $table->string('short_description')->default('')->change();
            $table->text('long_description')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('type_id')->unsigned()->default(null)->change();
            $table->string('tax_class')->default(null)->change();
            $table->string('short_description')->default(null)->change();
            $table->text('long_description')->nullable(false)->change();
        });
    }
}
