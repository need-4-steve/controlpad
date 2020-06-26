<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MoveProductRoleToProductVisibility extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_visibility', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->unsigned()->index();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->integer('visibility_id')->unsigned()->index();
            $table->foreign('visibility_id')->references('id')->on('visibilities')->onDelete('cascade');
        });
    
        $visibilites = DB::table('product_role')->select(['id', 'product_id', 'role_id as visibility_id'])->get();
        $visibilites = json_decode(json_encode($visibilites), true);
        DB::table('product_visibility')->insert($visibilites);
        Schema::table('product_role', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['role_id']);
        });
        Schema::dropIfExists('product_role');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('product_role', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->unsigned()->index();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->integer('role_id')->unsigned()->index();
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });
    
        $roles = DB::table('product_visibility')->select(['id', 'product_id', 'visibility_id as role_id'])->get();
        $roles = json_decode(json_encode($roles), true);
        DB::table('product_role')->insert($roles);
        Schema::table('product_visibility', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['visibility_id']);
        });
        Schema::dropIfExists('product_visibility');
    }
}
