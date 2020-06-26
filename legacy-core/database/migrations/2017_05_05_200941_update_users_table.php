<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\User;

class UpdateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('sponsor_id')->default(1)->unsigned()->change();
        });

        $users = User::whereNull('sponsor_id')->get();
        DB::beginTransaction();
        foreach ($users as $user) {
            if ($user->id === config('site.apex_user_id')) {
                $user->sponsor_id = 0;
            } else {
                $user->sponsor_id = config('site.apex_user_id');
            }
            $user->save();
        }
        DB::commit();
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
