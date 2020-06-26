<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use CPCommon\Pid\Pid;
use App\Models\SubscriptionUser;

class AddPidOnSubscriptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_user', function (Blueprint $table) {
            $table->string('pid', 25)->nullable()->index();
            $table->string('user_pid', 25)->nullable()->index();
        });
        $subscriptions = SubscriptionUser::whereNull('pid')->get();
        DB::beginTransaction();
        foreach ($subscriptions as $subscription) {
            $subscription->update(['pid' => Pid::create()]);
        }
        DB::commit();
        DB::table('subscription_user')
        ->join('users', 'users.id', '=', 'subscription_user.user_id')
        ->update([
            'subscription_user.user_pid' => DB::raw('users.pid'),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscription_user', function (Blueprint $table) {
            $table->dropColumn('pid');
            $table->dropColumn('user_pid');
        });
    }
}
