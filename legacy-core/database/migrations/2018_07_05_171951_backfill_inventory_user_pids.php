<?php

use Illuminate\Database\Migrations\Migration;

class BackfillInventoryUserPids extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function () {
            DB::update('update inventories as i join users as u on i.user_id = u.id set i.user_pid = u.pid where i.user_pid is null');
            DB::update('update inventories as i join users as u on i.owner_id = u.id set i.owner_pid = u.pid where i.owner_pid is null');
            DB::update('update products as p join users as u on p.user_id = u.id set p.user_pid = u.pid where p.user_pid is null');
            DB::update('update bundles as b join users as u on b.user_id = u.id set b.user_pid = u.pid where b.user_pid is null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Do nothing
    }
}
