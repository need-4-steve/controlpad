<?php

use Illuminate\Database\Seeder;

class AddServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('services')->insert([
            "name" => "AuthMan",
        ]);
    }
}
