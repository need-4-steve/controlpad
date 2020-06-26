<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call('AddTenantSeeder');
        $this->call('AddUsersSeeder');
        $this->call('AddServiceSeeder');
        $this->call('AddApiKeySeeder');
    }
}
