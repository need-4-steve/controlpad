<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexesToInventories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventories', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $doctrineTable = $sm->listTableDetails('inventories');
            if ($doctrineTable->hasIndex('owner_pid')) {
                $table->dropIndex('owner_pid');
            }
            if ($doctrineTable->hasIndex('user_pid')) {
                $table->dropIndex('user_pid');
            }
            $table->index('owner_pid');
            $table->index('user_pid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropIndex(['owner_pid']);
            $table->dropIndex(['user_pid']);
        });
    }
}
