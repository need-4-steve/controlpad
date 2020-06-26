<?php

use Illuminate\Database\Migrations\Migration;

class BackfillShippingRateUserPid extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::update(
            'update shipping_rates as sr ' .
            'left join users as u on u.id = sr.user_id and sr.user_pid is null ' .
            'set sr.user_pid = u.pid'
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::update('update shipping_rates set user_pid = null');
    }
}
