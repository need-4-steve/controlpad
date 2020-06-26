<?php

use Illuminate\Database\Seeder;

class AddUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            "email" => "superadmin@controlpad.com",
            "password" => app('hash')->make('password2'),
            "role" => "admin",
            "tenant_id" => 1
        ]);
        DB::table('users')->insert([
            "email" => "info@controlpad.com",
            "password" => app('hash')->make('password2'),
            "role" => "user",
            "tenant_id" => 1
        ]);
    }
}
