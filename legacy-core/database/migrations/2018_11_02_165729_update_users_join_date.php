<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUsersJoinDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `join_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP;");
        DB::statement("UPDATE `users` SET `join_date` = ADDTIME(`join_date`, '12:00:00')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `join_date` DATE;");
    }
}
