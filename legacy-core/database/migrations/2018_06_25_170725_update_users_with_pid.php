<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use CPCommon\Pid\Pid;
use App\Models\User;

class UpdateUsersWithPid extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('pid')->unique()->nullable();
        });
        \DB::beginTransaction();
        $users = User::withTrashed()->get();
        foreach ($users as $user) {
            $user->pid = Pid::create();
            $user->save();
        }
        \DB::commit();
        Schema::table('users', function (Blueprint $table) {
            $table->string('pid')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('pid');
        });
    }
}
