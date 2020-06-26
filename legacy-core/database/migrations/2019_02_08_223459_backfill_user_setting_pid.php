<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BackfillUserSettingPid extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('user_setting')
            ->join('users', 'users.id', '=', 'user_setting.user_id')
            ->where('user_setting.user_pid', '=', null)
            ->update([
                'user_setting.user_pid' => DB::raw('users.pid'),
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
