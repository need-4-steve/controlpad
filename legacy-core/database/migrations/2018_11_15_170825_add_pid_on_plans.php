<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use CPCommon\Pid\Pid;
use App\Models\Subscription;

class AddPidOnPlans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('pid', 25)->nullable()->index();
        });
        $subscriptions = Subscription::whereNull('pid')->get();
        DB::beginTransaction();
        foreach ($subscriptions as $subscription) {
            $subscription->update(['pid' => Pid::create()]);
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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('pid');
        });
    }
}
