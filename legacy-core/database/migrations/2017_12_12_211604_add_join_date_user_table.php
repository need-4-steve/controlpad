<?php

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;

class AddJoinDateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $date = DB::raw('CURRENT_TIMESTAMP');
        Schema::table('users', function (Blueprint $table) {
            $table->date('join_date');
        });
        DB::beginTransaction();
        $users = User::with('subscriptions')->get();
        foreach ($users as $user) {
            if ($user->subscriptions !== null) {
                $user->join_date = $user->subscriptions->created_at;
            } else {
                $user->join_date = $user->created_at;
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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('join_date');
        });
    }
}
