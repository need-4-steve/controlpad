<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveFreeTrialTimeOnUserSubcription extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('subscription_user', 'used_free_trial')) {
            Schema::table('subscription_user', function (Blueprint $table) {
                $table->dropColumn('used_free_trial');
            });
        }
    }

    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::table('subscription_user', function (Blueprint $table) {
            $table->boolean('used_free_trial')->default(false);
        });
    }
}
