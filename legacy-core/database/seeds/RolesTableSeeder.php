<?php

use Illuminate\Database\Seeder;
use App\Models\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        Role::create([
            "id" => 5,
            "name" => "Rep",
            "description" => "A fully-featured member and representative. Can only access features related to their sales and resources."
        ]);
        Role::create([
            "id" => 3,
            "name" => "Customer",
            "description" => "Someone who has purchased a product or service."
        ]);
        Role::create([
            "id" => 7,
            "name" => "Admin",
            "description" => "An administrative account with access to almost all features."
        ]);
        Role::create([
            "id" => 8,
            "name" => "Superadmin",
            "description" => "An administrative account with access to all features."
        ]);
        DB::commit();
    }
}
