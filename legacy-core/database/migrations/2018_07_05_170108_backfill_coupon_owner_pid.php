<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BackfillCouponOwnerPid extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::update('update coupons as c join users as u on u.id = c.owner_id set c.owner_pid = u.pid where c.owner_pid is null');
        Schema::table('coupons', function (Blueprint $table) {
            $table->string('owner_pid', 25)->nullable(false)->change(); // not nullable after backfill
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->string('owner_pid', 25)->nullable(true)->change();
        });
    }
}
