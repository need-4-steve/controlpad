<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MoveBundleRoleToBundleVisibility extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();
        Schema::create('bundle_visibility', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('bundle_id')->unsigned()->index();
            $table->foreign('bundle_id')->references('id')->on('bundles')->onDelete('cascade');
            $table->integer('visibility_id')->unsigned()->index();
            $table->foreign('visibility_id')->references('id')->on('visibilities')->onDelete('cascade');
        });
    
        $visibilites = DB::table('bundle_role')->select(['id', 'bundle_id', 'role_id as visibility_id'])->get();
        $visibilites = json_decode(json_encode($visibilites), true);
        DB::table('bundle_visibility')->insert($visibilites);
        Schema::table('bundle_role', function (Blueprint $table) {
            $table->dropForeign(['bundle_id']);
            $table->dropForeign(['role_id']);
        });
        Schema::dropIfExists('bundle_role');
        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::beginTransaction();
        Schema::create('bundle_role', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('bundle_id')->unsigned()->index();
            $table->foreign('bundle_id')->references('id')->on('bundles')->onDelete('cascade');
            $table->integer('role_id')->unsigned()->index();
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });
    
        $roles = DB::table('bundle_visibility')->select(['id', 'bundle_id', 'visibility_id as role_id'])->get();
        $roles = json_decode(json_encode($roles), true);
        DB::table('bundle_role')->insert($roles);
        Schema::table('bundle_visibility', function (Blueprint $table) {
            $table->dropForeign(['bundle_id']);
            $table->dropForeign(['visibility_id']);
        });
        Schema::dropIfExists('bundle_visibility');
        DB::commit();
    }
}
