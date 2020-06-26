<?php

use Illuminate\Database\Seeder;
use CPCommon\Pid\Pid;

class AddTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tenants')->insert([
            "name" => "ControlPad",
            "org_id" => Pid::create(),
            "read_host" => "localhost",
            "write_host" => "localhost",
            "db_name" => "homestead",
            "status" => "Paid",
            "domain" => "controlpaddev.com"
        ]);
    }
}
