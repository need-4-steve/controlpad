<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexesToBundles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bundles', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $doctrineTable = $sm->listTableDetails('bundles');
            if (!$doctrineTable->hasIndex('bundles_user_pid_index')) {
                $table->index('user_pid');
            }
            if (!$doctrineTable->hasIndex('bundles_user_id_index')) {
                $table->index('user_id');
            }
            $table->index('starter_kit');
            $table->index('type_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bundles', function (Blueprint $table) {
            $table->dropIndex(['user_pid']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['starter_kit']);
            $table->dropIndex(['type_id']);
        });
    }
}
