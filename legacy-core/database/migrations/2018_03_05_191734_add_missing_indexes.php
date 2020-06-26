<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMissingIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /* FULLTEXT is unique to mysql and isn't available throught the ORM.
         * FULLTEXT indexes differently then a normal index.
         * It will index it by each word within the text field instead of indexing by the whole field.
         */
        DB::statement('ALTER TABLE products ADD FULLTEXT full(short_description, long_description)');
        Schema::table('products', function (Blueprint $table) {
            $table->index('user_id');
        });

        Schema::table('variants', function (Blueprint $table) {
            $table->index('product_id');
            $table->index('name');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->index('manufacturer_sku');
            $table->index('size');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE products DROP INDEX full');
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('variants', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
            $table->dropIndex(['name']);
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex(['manufacturer_sku']);
            $table->dropIndex(['size']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });
    }
}
